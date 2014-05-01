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
