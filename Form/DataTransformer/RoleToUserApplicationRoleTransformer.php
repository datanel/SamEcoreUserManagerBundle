<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class RoleToUserApplicationRoleTransformer implements DataTransformerInterface
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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

    public function reverseTransform($user)
    {
        if (!$user) {
            return $user;
        }

        return $user;
    }
}
