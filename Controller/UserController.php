<?php

namespace CanalTP\SamEcoreUserManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    /**
     * Lists all User entities.
     */
    public function indexAction($page)
    {
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
        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUserBy(array('id' => $id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createForm('sam_user_form', $entity);

        return $this->render(
            'CanalTPSamEcoreUserManagerBundle:User:edit.html.twig',
            array(
                'entity'    => $entity,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    /**
     * Edits an existing User entity.
     */
    public function updateAction(Request $request, $id)
    {
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
                $userManager = $this->container->get('fos_user.user_manager');
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
}
