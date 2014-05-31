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
        $user->setIsSuperAdmin(true);

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
                'username'  => 'Sam',
                'firstname' => 'Samuel',
                'lastname'  => 'Dictator',
                'email'     => 'sam@canaltp.fr',
                'password'  => 'sam',
                'roles'     => array('role-super-admin-sam')
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
