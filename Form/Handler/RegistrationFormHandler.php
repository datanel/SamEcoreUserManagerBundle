<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use Symfony\Component\Form\FormError;
use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseRegistrationFormHandler;
use CanalTP\SamEcoreApplicationManagerBundle\Exception\OutOfBoundsException;
use CanalTP\SamEcoreApplicationManagerBundle\Component\BusinessComponentRegistry;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;


class RegistrationFormHandler extends BaseRegistrationFormHandler
{
    private $businessRegistry;
    private $objectManager;

    public function setObjectManager($om)
    {
        $this->objectManager = $om;
    }

    /**
     * @param boolean $confirmation
     * @see about setPlainPassword -> https://github.com/FriendsOfSymfony/FOSUserBundle/issues/898
     */
    public function save(User $user, $confirmation = false)
    {
        if ($confirmation) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }
            $user->setPlainPassword(md5(time()));
            $this->userManager->updateUser($user);
            $this->mailer->sendConfirmationEmailMessage($user);
        } else {
            $user->setEnabled(true);
        }

        $this->userManager->updateUser($user);
    }
}
