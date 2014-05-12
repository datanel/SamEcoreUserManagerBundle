<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use CanalTP\SamEcoreApplicationManagerBundle\Security\BusinessComponentRegistry;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;

use FOS\UserBundle\Form\Handler\ProfileFormHandler as BaseProfileFormHandler;

class ProfileFormHandler extends BaseProfileFormHandler
{
    private $businessRegistry;

    public function processUser(UserRegistration $userRegistration)
    {
        $this->form->setData($userRegistration);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->save($userRegistration);

                return true;
            }

            // Reloads the user to reset its username. This is needed when the
            // username or password have been changed to avoid issues with the
            // security layer.
            $this->userManager->reloadUser($userRegistration->user);
        }

        return false;
    }

    /**
     * @param boolean $confirmation
     */
    protected function save(UserRegistration $userRegistration)
    {
        $user = $userRegistration->user;

        $selectedApps = array();
        foreach ($userRegistration->applications as $selectedApp) {
            $selectedApps[] = $selectedApp->getId();
        }

        foreach ($userRegistration->rolesAndPerimetersByApplication as $app) {
            if (in_array($app->getId(), $selectedApps)) {
                $user->getUserRoles()->clear();
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
