<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;

use FOS\UserBundle\Form\Handler\ProfileFormHandler as BaseProfileFormHandler;

class ProfileFormHandler extends BaseProfileFormHandler
{
    public function processUser(UserRegistration $userRegistration)
    {
        $this->form->setData($userRegistration);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }

            // Reloads the user to reset its username. This is needed when the
            // username or password have been changed to avoid issues with the
            // security layer.
            $this->userManager->reloadUser($user);
        }

        return false;
    }

    protected function onSuccess(UserInterface $user)
    {
        $this->userManager->updateUser($user);
    }
}
