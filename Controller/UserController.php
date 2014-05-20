<?php

namespace CanalTP\SamEcoreUserManagerBundle\Controller;

use CanalTP\SamCoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use CanalTP\SamEcoreApplicationManagerBundle\Exception\OutOfBoundsException;
use CanalTP\SamEcoreUserManagerBundle\Form\Type\ProfilFormType;
use Symfony\Component\Form\Form;

class UserController extends AbstractController
{
    /**
     * Lists all User entities.
     */
    public function indexAction($page)
    {
        $this->isAllowed('BUSINESS_VIEW_USER');

        $userListProcessor = $this->container->get('canaltp.role.processor');
        $entities          = $userListProcessor->getVisibleUsers($page);

        $deleteFormViews = array();
        foreach ($entities as $entitie) {
            $id                   = $entitie->getId();
            $deleteForm           = $this->createDeleteForm($id);
            $deleteFormViews[$id] = $deleteForm->createView();
        }

        $params = array(
            'entities'     => $entities,
            'delete_forms' => $deleteFormViews,
        );

        $pagination = $userListProcessor->getPagination();
        if ($pagination > 1) {
            $params['pagination'] = array(
                'current' => $page,
                'total'   => $pagination,
            );
        }

        return $this->render(
            'CanalTPSamEcoreUserManagerBundle:User:index.html.twig',
            $params
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     */
    public function editAction($id)
    {
        $this->isAllowed('BUSINESS_MANAGE_USER');

        $userFormModel = $this->getUserFormModel($id);

        $form = $this->container->get('fos_user.profile.form');
        $formHandler = $this->container->get('fos_user.profile.form.handler');
        
        $process = $formHandler->processUser($userFormModel);
        if ($process) {
            $this->get('session')->getFlashBag()->add(
                'success',
                'profile.flash.updated'
            );
            $url = $this->generateUrl('sam_user_list');

            return $this->redirect($url);
        }

        return $this->render(
            'CanalTPSamEcoreUserManagerBundle:User:edit.html.twig',
            array(
                'user'    => $userFormModel->user,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Edits an existing User entity.
     */
    public function updateAction(Request $request, $id)
    {
        $this->isAllowed('BUSINESS_MANAGE_USER');

        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUserBy(array('id' => $id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createForm('sam_user_form', $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $userManager->updateUser($entity);

            return $this->redirect(
                $this->generateUrl('sam_user_list', array('id' => $id))
            );
        }

        return $this->render(
            'CanalTPSamEcoreUserManagerBundle:User:edit.html.twig',
            array(
                'entity'    => $entity,
                'edit_form' => $editForm->createView(),
            )
        );
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
        $user = $userManager->findUser($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $apps = array();
        foreach ($user->getUserRoles() as $role) {
            $application = $role->getApplication();
            if (!isset($apps[$application->getId()])) {
                try{
                    $apps[$application->getId()] = $role->getApplication();
                    $apps[$application->getId()]->getRoles()->clear();

                    $userPerimeters = $this->get('sam.business_component')
                        ->getBusinessComponent($application->getCanonicalName())
                        ->getPerimetersManager()
                        ->getUserPerimeters($user);

                    $apps[$application->getId()]->setPerimeters($userPerimeters);
                } catch (\Exception $e) {
                }
            }
            $apps[$application->getId()]->addRole($role);
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
        $userFormModel->rolesAndPerimetersByApplication = $apps;

        return $userFormModel;
    }

    public function editProfilProcessForm($user)
    {
        $this->get('sam_user.user_manager')->save($user);
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
            array('form' => $form->createView())
        );
    }

    public function toolbarAction()
    {
        return $this->render(
            'CanalTPSamEcoreUserManagerBundle:User:toolbar.html.twig'
        );
    }
}
