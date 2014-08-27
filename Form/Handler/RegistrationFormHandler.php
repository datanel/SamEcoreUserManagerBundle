<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use CanalTP\SamEcoreApplicationManagerBundle\Exception\OutOfBoundsException;
use CanalTP\SamEcoreApplicationManagerBundle\Component\BusinessComponentRegistry;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;
use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseRegistrationFormHandler;
use Symfony\Component\Form\FormError;

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
     */
    public function process($confirmation = false)
    {
        $userRegistration = new UserRegistration;
        $userRegistration->user = $this->createUser();
        $this->form->setData($userRegistration);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->save($userRegistration, $confirmation);

                return true;
            }
        }

        return false;
    }

    /**
     * @param boolean $confirmation
     */
    protected function save(UserRegistration $userRegistration, $confirmation)
    {
        $user = $userRegistration->user;
        if ($confirmation) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->userManager->updateUser($user);
            $this->mailer->sendConfirmationEmailMessage($user);
        } else {
            $user->setEnabled(true);
        }

        $user->setCustomer($userRegistration->customer);
        $this->userManager->updateUser($user);
    }

    public function setBusinessRegistry(BusinessComponentRegistry $businessRegistry)
    {
        $this->businessRegistry = $businessRegistry;
    }

    public function getBusinessRegistry()
    {
        return $this->businessRegistry;
    }
}
