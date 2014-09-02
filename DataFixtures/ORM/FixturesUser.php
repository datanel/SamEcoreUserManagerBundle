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
        //TODO:
        $users = array(
            array(
                'username'  => 'Sam',
                'firstname' => 'Samuel',
                'lastname'  => 'Dictator',
                'email'     => 'sam@canaltp.fr',
                'password'  => 'sam',
                'roles'     => array(
                    'role-super-admin-sam',
                    'role-user-sam',
                    'role-referent-sam',
                    'role-obs-sam',
                    'role-admin-sam',
                    'role-obs-mtt',
                    'role-user-mtt',
                    'role-admin-mtt',
                    'ROLE_ADMIN_MATRIX',
                    'ROLE_USER_MATRIX',
                    'ROLE_ROOT_REAL_TIME',
                    'ROLE_ADMIN_REAL_TIME',
                    'ROLE_USER_REAL_TIME'
                )
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
