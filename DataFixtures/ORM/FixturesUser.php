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
                'username'  => 'remy',
                'firstname' => 'Rémy',
                'lastname'  => 'Abi Khalil',
                'email'     => 'remy@canaltp.fr',
                'password'  => 'remy',
            ),
            array(
                'username'  => 'david',
                'firstname' => 'David',
                'lastname'  => 'Quintanel',
                'email'     => 'david.quintanel@canaltp.fr',
                'password'  => 'david',
            ),
            array(
                'username'  => 'Maître de son quartier et encore',
                'firstname' => 'Kévin',
                'lastname'  => 'ZIEMIANSKI',
                'email'     => 'kevin.ziemianski@canaltp.fr',
                'password'  => 'kevin',
            ),
            array(
                'username'  => 'matrix_admin',
                'firstname' => 'matrix',
                'lastname'  => 'admin',
                'email'     => 'matrix_admin@canaltp.fr',
                'password'  => 'matrix_admin',
                'roles' => array('role-admin-matrix')
            ),
            array(
                'username'  => 'matrix_voyage',
                'firstname' => 'matrix',
                'lastname'  => 'voyage',
                'email'     => 'matrix_voyage@canaltp.fr',
                'password'  => 'matrix_voyage',
                'roles' => array('role-user-matrix')
            ),
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
