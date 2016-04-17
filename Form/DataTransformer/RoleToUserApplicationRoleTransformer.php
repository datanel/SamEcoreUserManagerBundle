<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class RoleToUserApplicationRoleTransformer implements DataTransformerInterface
{
    private $om;
    private $authorizationChecker;
    private $currentUser;

    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->om = $om;
        $this->currentUser = $tokenStorage->getToken()->getUser();
        $this->authorizationChecker = $authorizationChecker;
    }

    public function transform($user)
    {
        if ($user === null) {
            return ($user);
        }

        $customer = $this->om->getRepository('CanalTPSamCoreBundle:Customer')->find($user->getCustomer());

        foreach ($customer->getActiveCustomerApplications() as $application) {
            $user->addApplication($application->getApplication());
        }
        return $user;
    }

    private function getRoles($officialRoles, $submitedRoles)
    {
        $userRoles = array();

        foreach ($submitedRoles as $role) {
            if ($this->authorizationChecker->isGranted('BUSINESS_MANAGE_USER')
                || array_key_exists($role->getId(), $officialRoles)) {
                $userRoles[] = $role;
            }
        }
        return ($userRoles);
    }

    public function reverseTransform($user)
    {
        if (!$user) {
            return $user;
        }
        $userRoles = array();
        $currentUserRoles = $this->currentUser->getRoles();

        foreach ($user->getApplications() as $application) {
            $userRoles = array_merge(
                $userRoles,
                $this->getRoles(
                    $currentUserRoles,
                    $application->getRoles()
                )
            );
        }
        $user->setUserRoles($userRoles);
        return $user;
    }
}
