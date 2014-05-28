<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use CanalTP\SamEcoreApplicationManagerBundle\Security\BusinessComponentRegistry;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;
use CanalTP\SamEcoreApplicationManagerBundle\Exception\OutOfBoundsException;

use FOS\UserBundle\Form\Handler\ProfileFormHandler as BaseProfileFormHandler;
use Symfony\Component\Form\FormError;

class ProfileFormHandler extends BaseProfileFormHandler
{
    private $businessRegistry;

    private function checkElementError($appBoxForm, $name)
    {
        return ($appBoxForm->has($name) && count($appBoxForm->get($name)->getViewData()) > 0);
    }

    private function getRolesAndPerimetersFormByAppId($appId)
    {
        $applications = $this->form->get('rolesAndPerimetersByApplication');

        foreach ($applications as $appBoxForm) {
            if ($appBoxForm->getData()->getId() == $appId) {
                return ($appBoxForm);
            }
        }
        return (null);
    }


    private function checkApplicationsValidation()
    {
        $applications = $this->form->get('applications')->getData();
        $result = true;

        if (count($applications) == 0) {
            $this->form->get('applications')->addError(new FormError('ctp_user.form.error.field.application.not_blank'));

            return (false);
        }
        foreach ($applications as $application) {
            $appBoxForm = $this->getRolesAndPerimetersFormByAppId($application->getId());

            if ($this->checkElementError($appBoxForm, 'roles') && count($application->getRoles()) == 0)
            {
                $appBoxForm->get('roles')->addError(new FormError('ctp_user.form.error.field.roles.not_blank'));
                $result = false;
            }
            if ($this->checkElementError($appBoxForm, 'perimeters') && count($application->getPerimeters()) == 0) {
                $appBoxForm->get('perimeters')->addError(new FormError('ctp_user.form.error.field.perimeters.not_blank'));
                $result = false;
            }
        }

        return ($result);
    }

    public function processUser(UserRegistration $userRegistration)
    {
        $this->form->setData($userRegistration);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid() && $this->checkApplicationsValidation()) {
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

        $user->getUserRoles()->clear();
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

                    $businessPerimeterManager->deleteUserPerimeters($user);
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
