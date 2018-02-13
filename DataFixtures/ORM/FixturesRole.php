<?php

namespace CanalTP\SamEcoreUserManagerBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamCoreBundle\DataFixtures\ORM\RoleTrait;

class FixturesRole extends AbstractFixture implements OrderedFixtureInterface
{
    use RoleTrait;

    private $roles = array(
        array(
            'name'          => 'Utilisateur',
            'reference'     => 'user-sam',
            'application'   => 'app-samcore',
            'isEditable'    => true,
            'permissions'   => array(
                'BUSINESS_VIEW_USER',
                'BUSINESS_MANAGE_USER',
                'BUSINESS_MANAGE_USER_PERIMETER',
                'BUSINESS_VIEW_ROLE',
                'BUSINESS_MANAGE_ROLE'
            )
        ),
        array(
            'name'          => 'Référent',
            'reference'     => 'referent-sam',
            'application'   => 'app-samcore',
            'isEditable'    => true,
            'permissions'  => array()
        ),
        array(
            'name'          => 'Observateur',
            'reference'     => 'obsevateur-sam',
            'application'   => 'app-samcore',
            'isEditable'    => true,
            'permissions'  => array()
        ),
        array(
            'name'          => 'Administrateur',
            'reference'     => 'admin-sam',
            'application'   => 'app-samcore',
            'isEditable'    => true,
            'permissions'  => array(
                'BUSINESS_VIEW_USER',
                'BUSINESS_MANAGE_USER',
                'BUSINESS_MANAGE_USER_PERIMETER',
                'BUSINESS_MANAGE_PERMISSION',
                'BUSINESS_VIEW_ROLE',
                'BUSINESS_MANAGE_ROLE'
            )
        ),
        array(
            'name'          => 'Super Admin',
            'reference'     => 'super-admin-sam',
            'application'   => 'app-samcore',
            'isEditable'    => true,
            'permissions'  => array(
                'BUSINESS_VIEW_USER',
                'BUSINESS_MANAGE_USER',
                'BUSINESS_MANAGE_USER_PERIMETER',
                'BUSINESS_MANAGE_PERMISSION',
                'BUSINESS_VIEW_ROLE',
                'BUSINESS_MANAGE_ROLE',
            )
        )
    );

    public function load(ObjectManager $om)
    {
         foreach ($this->roles as $role) {
            $this->createApplicationRole($om,  $role);
        }
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 2;
    }
}
 