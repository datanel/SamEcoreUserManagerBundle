<?php

namespace CanalTP\SamEcoreUserManagerBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class FixturesUser extends AbstractFixture implements OrderedFixtureInterface
{
    private function createUser(objectManager $em, $data)
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setFirstName($data['firstname']);
        $user->setLastName($data['lastname']);
        $user->setEnabled(true);
        $user->setEmail($data['email']);
        $user->setPlainPassword($data['password']);
        $user->setRoles($data['roles']);
        $em->persist($user);

        return ($user);
    }

    public function load(ObjectManager $em)
    {
        $users = array(
            array(
                'username'  => 'akambi',
                'firstname' => 'Akambi',
                'lastname'  => 'Fagbohoun',
                'email'     => 'akambi-fagbohoun@canaltp.fr',
                'password'  => 'akambi',
                'roles'     => array('ROLE_ADMIN')
            ),
            array(
                'username'  => 'remy',
                'firstname' => 'Remy',
                'lastname'  => 'Abi',
                'email'     => 'remy@canaltp.fr',
                'password'  => 'remy',
                'roles'     => array('ROLE_ADMIN')
            ),
            array(
                'username'  => 'david',
                'firstname' => 'David',
                'lastname'  => 'Quintanel',
                'email'     => 'david.quintanel@canaltp.fr',
                'password'  => 'david',
                'roles'     => array('ROLE_ADMIN')
            ),
            array(
                'username'  => 'Maître du monde',
                'firstname' => 'Kévin',
                'lastname'  => 'ZIEMIANSKI',
                'email'     => 'kevin.ziemianski@canaltp.fr',
                'password'  => 'kevin',
                'roles'     => array('SO_USELESSS')
            ),
        );

        // $sim = $this->initSim($em);
        foreach ($users as $user) {
            $this->createUser(
                $em,
                $user
            );
        }

        $em->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 2;
    }
}
