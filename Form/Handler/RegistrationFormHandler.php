<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use CanalTP\SamEcoreApplicationManagerBundle\Exception\OutOfBoundsException;
use CanalTP\SamEcoreApplicationManagerBundle\Security\BusinessComponentRegistry;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;
use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseRegistrationFormHandler;
use Symfony\Component\Form\FormError;

class RegistrationFormHandler extends BaseRegistrationFormHandler
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
            $this->form->get('applications')->addError(new FormError('user.field.application.not_blank'));

            return (false);
        }
        foreach ($applications as $application) {
            $appBoxForm = $this->getRolesAndPerimetersFormByAppId($application->getId());

            if ($this->checkElementError($appBoxForm, 'roles') && count($application->getRoles()) == 0)
            {
                $appBoxForm->get('roles')->addError(new FormError('user.field.roles.not_blank'));
                $result = false;
            }
            if ($this->checkElementError($appBoxForm, 'perimeters') && count($application->getPerimeters()) == 0) {
                $appBoxForm->get('perimeters')->addError(new FormError('user.field.perimeters.not_blank'));
                $result = false;
            }
        }

        return ($result);
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

            if ($this->form->isValid() && $this->checkApplicationsValidation()) {
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
