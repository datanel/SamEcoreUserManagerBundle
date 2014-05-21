<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use CanalTP\SamEcoreApplicationManagerBundle\Exception\OutOfBoundsException;
use CanalTP\SamEcoreApplicationManagerBundle\Security\BusinessComponentRegistry;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;
use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseRegistrationFormHandler;

class RegistrationFormHandler extends BaseRegistrationFormHandler
{
    private $businessRegistry;

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

            try {
                $this->userManager->updateUser($user);
            } catch (\Exception $e) {
                //@todo remove
                var_dump($e->getMessage());
                var_dump("Echec de la création de l'utilisateur. Possible qu'un élément soit en doublon.");
                die(__CLASS__ . ' : ' . __LINE__);
            }

            $this->mailer->sendConfirmationEmailMessage($user);
        } else {
            $user->setEnabled(true);
        }

        $selectedApps = array();
        foreach ($userRegistration->applications as $selectedApp) {
            $selectedApps[] = $selectedApp->getId();
        }

        foreach ($userRegistration->rolesAndPerimetersByApplication as $app) {
            if (in_array($app->getId(), $selectedApps)) {
                foreach ($app->getRoles() as $role) {
                    $user->addUserRole($role);
                }
            }
        }

        $this->userManager->updateUser($user);

        // Add Perimeters to the user
        foreach ($userRegistration->rolesAndPerimetersByApplication as $app) {
            if (in_array($app->getId(), $selectedApps)) {
                try {
                    $businessPerimeterManager = $this->businessRegistry
                        ->getBusinessComponent($app->getCanonicalName())
                        ->getPerimetersManager();

                    foreach ($app->getPerimeters() as $perimeter) {
                        $businessPerimeterManager->addUserToPerimeter($user, $perimeter);
                    }
                } catch (OutOfBoundsException $e) {
                    // If no business component found, we do not break anything
                } catch (\Exception $e) {
                }
            }
        }
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
