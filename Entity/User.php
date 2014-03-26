<?php

namespace CanalTP\SamEcoreUserManagerBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;

class User extends BaseUser
{
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $groups;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $roleGroupByApplications;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $applicationRoles;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->applicationRoles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     *
     * @param  string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param  string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Add applicationRoles
     *
     * @param \CanalTP\SamCoreBundle\Entity\ApplicationRole $applicationRoles
     * @return User
     */
    public function addApplicationRole(\CanalTP\SamCoreBundle\Entity\ApplicationRole $applicationRoles)
    {
        $this->applicationRoles[] = $applicationRoles;

        return $this;
    }

    /**
     * Remove applicationRoles
     *
     * @param \CanalTP\SamCoreBundle\Entity\ApplicationRole $applicationRoles
     */
    public function removeApplicationRole(\CanalTP\SamCoreBundle\Entity\ApplicationRole $applicationRoles)
    {
        $this->applicationRoles->removeElement($applicationRoles);
    }

    /**
     * Get applicationRoles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApplicationRoles()
    {
        return $this->applicationRoles;
    }

    /**
     * Set currentApplicationRoles
     *
     * @return Role
     */
    public function setApplicationRoles($applicationRoles)
    {
        $this->applicationRoles = $applicationRoles;

        return ($this);
    }

    /**
     * Add roleGroupByApplication
     *
     * @param \CanalTP\SamCoreBundle\Entity\ApplicationRole $roleParent
     * @return Role
     */
    public function addRoleGroupByApplication(\CanalTP\SamCoreBundle\Entity\ApplicationRole $roleGroupByApplication)
    {
        $this->roleGroupByApplications[] = $roleGroupByApplication;

        return $this;
    }

    /**
     * Remove roleGroupByApplication
     *
     * @param \CanalTP\SamCoreBundle\Entity\Application $roleParent
     */
    public function removeRoleGroupByApplication(\CanalTP\SamCoreBundle\Entity\ApplicationRole $roleGroupByApplication)
    {
        $this->roleGroupByApplications->removeElement($roleGroupByApplication);
    }

    /**
     * Get roleGroupByApplications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoleGroupByApplications()
    {
        return $this->roleGroupByApplications;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Appeler avant la mise à jour d'un objet en base de donnée
     */
    public function onPostLoad()
    {
        $aRoles = array();
        foreach ($this->getApplicationRoles() as $applicationRole) {
            $aRoles[] = $applicationRole->getCanonicalRole();
        }
        $this->setRoles($aRoles);
    }
}
