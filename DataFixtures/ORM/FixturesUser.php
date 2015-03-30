<?php

namespace CanalTP\SamEcoreUserManagerBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamCoreBundle\DataFixtures\ORM\UserTrait;

class FixturesUser extends AbstractFixture implements OrderedFixtureInterface
{
    use UserTrait;

    // TODO: Add all aplications role.
    private $users = array(
        array(
            'id'        => null,
            'username'  => 'Admin',
            'firstname' => 'Super Admin',
            'lastname'  => 'Super Admin',
            'email'     => 'admin@canaltp.fr',
            'password'  => 'admin',
            'roles'     => array(
                'role-super-admin-sam',
                'role-admin-sam',
                'role-user-sam',
                'role-referent-sam',
                'role-obsevateur-sam'
            ),
            'customer'  => 'customer-canaltp'
        )
    );

    public function load(ObjectManager $om)
    {
        foreach ($this->users as $userData) {
            $userEntity = $this->createUser($om, $userData);
        }
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 4;
    }
}
