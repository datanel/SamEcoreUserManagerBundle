<?php

namespace CanalTP\SamEcoreUserManagerBundle\Controller;

use CanalTP\SamCoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use CanalTP\SamEcoreApplicationManagerBundle\Exception\OutOfBoundsException;
use CanalTP\SamEcoreUserManagerBundle\Form\Type\ProfilFormType;
use Symfony\Component\Form\Form;

class UserController extends AbstractController
{
    private $userManager = null;


    /**
     * Lists all User entities.
     */
    public function listAction()
    {
        $this->isAllowed('BUSINESS_VIEW_USER');

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $customers = $this->container->get('sam_core.customer')->findAllToArray();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        if ($isSuperAdmin) {
            $entities = $this->container->get('sam_user.user_manager')->findUsers();
        } else {
            $entities = $userManager->findUsersBy(array('customer' => $user->getCustomer()));
        }


        $deleteFormViews = array();
        foreach ($entities as $entity) {
            $id                   = $entity->getId();
            $deleteForm           = $this->createDeleteForm($id);
            $deleteFormViews[$id] = $deleteForm->createView();
            if ($entity->getCustomer()) {
                $entity->setCustomer($customers[$entity->getCustomer()]);
            }
        }

        return $this->render(
            'CanalTPSamEcoreUserManagerBundle:User:list.html.twig',
            array(
                'entities'     => $entities,
                'isSuperAdmin' => $isSuperAdmin,
                'delete_forms' => $deleteFormViews,
            )
        );
    }

    private function processForm(Request $request, $userFormModel)
    {
        $formHandler = $this->container->get('fos_user.profile.form.handler');

        if ($formHandler->processUser($userFormModel)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                'profile.flash.updated'
            );

            return $this->redirect($this->generateUrl('sam_user_list'));
        }

        return (null);
    }

    public function editAction(Request $request, $id)
    {
        $this->isGranted('BUSINESS_MANAGE_USER');

        $this->userManager = $this->get('sam_user.user_manager');
        $form = $this->container->get('fos_user.profile.form');
        $userFormModel = $this->getUserFormModel($id);
        $render = $this->processForm($request, $userFormModel);

        if (!$render) {
            return $this->render(
                'CanalTPSamEcoreUserManagerBundle:User:edit.html.twig',
                array(
                    'user' => $userFormModel->user,
                    'form' => $form->createView(),
                )
            );
        }
        return ($render);
    }

    /**
     * Deletes a User entity.
     */
    public function deleteAction(Request $request, $id)
    {
        $this->isAllowed('BUSINESS_MANAGE_USER');

        $form = $this->createDeleteForm($id);

        if ($request->getMethod() == 'GET') {
            $userManager = $this->container->get('fos_user.user_manager');
            $entity = $userManager->findUserBy(array('id' => $id));

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            return $this->render(
                'CanalTPSamEcoreUserManagerBundle:User:delete.html.twig',
                array(
                    'entity'      => $entity,
                    'delete_form' => $form->createView(),
                )
            );
        } else {
            $form->bind($request);

            if ($form->isValid()) {
                if ($this->getUser()->getId() == $id) {
                    throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Seriously, you shouldn\'t delete your account.');
                }

                //Use sam user manager ;)
                $userManager = $this->container->get('sam_user.user_manager');
                $entity = $userManager->findUserBy(array('id' => $id));

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find User entity.');
                }

                $userManager->deleteUser($entity);
            }

            return $this->redirect($this->generateUrl('sam_user_list'));
        }
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }

    private function getUserFormModel($id)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $apps = array();
        $appsPA = array();
        foreach ($user->getUserRoles() as $role) {
            $application = $role->getApplication();
            if (!isset($apps[$application->getId()])) {
                try {
                    $appRolesPerims = new \CanalTP\SamEcoreApplicationManagerBundle\Form\Model\ApplicationRolesPerimeters();
                    $appRolesPerims->application = $application;
                    $appsPA[$application->getId()] = $appRolesPerims;
                    $appsPA[$application->getId()]->application->getRoles()->clear();
                    $apps[$application->getId()] = $application;

                    $userPerimeters = $this->get('sam.business_component')
                        ->getBusinessComponent($application->getCanonicalName())
                        ->getPerimetersManager()
                        ->getUserPerimeters($user);

                    $appsPA[$application->getId()]->application->setPerimeters($userPerimeters);
                } catch (\Exception $e) {
                }
            }
            $appsPA[$application->getId()]->application->addRole($role);

            if ($role->getCanonicalName() == 'ROLE_SUPER_ADMIN') {
                $appsPA[$application->getId()]->superAdmin = true;
            }
        }

        $apps = array_values($apps);

        // A user may not have roles but perimeters so we have to check this (for the checkboxes Applications) I don't like it
        if (empty($apps)) {
            $applications = $this->get('doctrine')->getRepository('CanalTPSamCoreBundle:Application')->findAll();
            foreach ($applications as $application) {
                try {
                    $userPerimeters = $this->get('sam.business_component')
                        ->getBusinessComponent($application->getCanonicalName())
                        ->getPerimetersManager()
                        ->getUserPerimeters($user);

                    if (count($userPerimeters)) {
                        $application->setPerimeters($userPerimeters);
                        $application->setRoles(array());
                        $appRolesPerims = new \CanalTP\SamEcoreApplicationManagerBundle\Form\Model\ApplicationRolesPerimeters();
                        $appRolesPerims->application = $application;
                        $appsPA[] = $appRolesPerims;
                        $apps[] = $application;
                    }
                } catch (OutOfBoundsException $e) {
                    // If no business component found, we do not break anything
                } catch (\Exception $e) {
                }
            }
        }

        $userFormModel = new \CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;
        $userFormModel->user = $user;
        $userFormModel->applications = $apps;
        $userFormModel->rolesAndPerimetersByApplication = array_values($appsPA);

        return $userFormModel;
    }

    public function editProfilProcessForm($user)
    {
        $this->get('sam.user_manager')->updateUser($user);
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('ctp_user.profil.edit.validate')
        );
    }

    /**
     * Displays a form to edit profil of current user.
     */
    public function editProfilAction()
    {
        $app = $this->get('canal_tp_sam.application.finder')->getCurrentApp();
        $id = $this->get('security.context')->getToken()->getUser()->getId();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array('id' => $id));
        $form = $this->createForm(
            new ProfilFormType(),
            $user
        );

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $this->editProfilProcessForm($user);
        }
        return $this->render(
            'CanalTPSamEcoreUserManagerBundle:User:profil.html.twig',
            array(
                'form' => $form->createView(),
                'defaultAppHomeUrl' => $app->getDefaultRoute()
            )
        );
    }

    public function toolbarAction()
    {
        $appCanonicalName = $this->get('canal_tp_sam.application.finder')->getCurrentApp()->getCanonicalName();

        return $this->render(
            'CanalTPSamEcoreUserManagerBundle:User:toolbar.html.twig',
            array('currentAppName' => $appCanonicalName)
        );
    }
}
