<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Common\Persistence\ObjectManager;

class RoleToUserApplicationRoleTransformer implements DataTransformerInterface
{
    private $om;
    private $securityContext;

    public function __construct(ObjectManager $om, SecurityContext $securityContext)
    {
        $this->om = $om;
        $this->securityContext = $securityContext;
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
            if (array_key_exists($role->getId(), $officialRoles)) {
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
        $currentUser = $this->securityContext->getToken()->getUser();
        $userRoles = array();

        foreach ($user->getApplications() as $application) {
            $userRoles = array_merge(
                $userRoles,
                $this->getRoles(
                    $currentUser->getRoles(),
                    $application->getRoles()
                )
            );
        }
        $user->setUserRoles($userRoles);
        return $user;
    }
}
