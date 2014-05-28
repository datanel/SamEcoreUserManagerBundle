<?php

namespace CanalTP\SamEcoreUserManagerBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class FixturesUser extends AbstractFixture implements OrderedFixtureInterface
{
    private function createUser($data)
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setFirstName($data['firstname']);
        $user->setLastName($data['lastname']);
        $user->setEnabled(true);
        $user->setEmail($data['email']);
        $user->setPlainPassword($data['password']);
        $user->setIsSuperAdmin(false);

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $role) {
                $user->addUserRole($this->getReference($role));
            }
        }

        return $user;
    }

    public function load(ObjectManager $em)
    {
        $users = array(
            array(
                'username'  => 'rt_user',
                'firstname' => 'realtime',
                'lastname'  => 'user',
                'email'     => 'real_time_user@canaltp.fr',
                'password'  => 'realtime_user',
                'roles' => array('role-user-realtime')
            ),
            array(
                'username'  => 'rt_admin',
                'firstname' => 'realtime',
                'lastname'  => 'admin',
                'email'     => 'real_time_admin@canaltp.fr',
                'password'  => 'realtime_admin',
                'roles' => array('role-admin-realtime')
            ),
        );

        foreach ($users as $user) {
            $entity = $this->createUser($user);

            $em->persist($entity);

            $this->addReference('user-'.$user['username'], $entity);
        }

        $em->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 3;
    }
}
