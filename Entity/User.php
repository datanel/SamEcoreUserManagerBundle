<?php

namespace CanalTP\SamEcoreUserManagerBundle\Entity;

use FOS\UserBundle\Model\User as AbstractUser;
use Doctrine\Common\Collections\ArrayCollection;

class User extends AbstractUser
{
    const ROLE_ADMIN = 'ROLE_ADMIN';

    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $usernameCanonical;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $emailCanonical;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * The salt to use for hashing
     *
     * @var string
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     */
    protected $password;

    /**
     * @var \DateTime
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     */
    protected $confirmationToken;

    /**
     * @var \DateTime
     */
    protected $passwordRequestedAt;

    /**
     * @var boolean
     */
    protected $locked;

    /**
     * @var boolean
     */
    protected $expired;

    /**
     * @var \DateTime
     */
    protected $expiresAt;

    /**
     * @var array
     */
    protected $role;

    /**
     * @var boolean
     */
    protected $credentialsExpired;

    /**
     * @var \DateTime
     */
    protected $credentialsExpireAt;

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
        $this->applicationRoles = new ArrayCollection();
    }

    /**
     * Set firstName
     *
     * @param  string $firstName
     * @return User
     */
    public function setFirstname($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param  string $lastName
     * @return User
     */
    public function setLastname($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastName;
    }

    /**
     * Add applicationRoles
     *
     * @param \CanalTP\SamCoreBundle\Entity\UserApplicationRole $applicationRoles
     * @return User
     */
    public function addApplicationRole(\CanalTP\SamCoreBundle\Entity\UserApplicationRole $applicationRoles)
    {
        $this->applicationRoles[] = $applicationRoles;

        return $this;
    }

    /**
     * Remove applicationRoles
     *
     * @param \CanalTP\SamCoreBundle\Entity\UserApplicationRole $applicationRoles
     */
    public function removeApplicationRole(\CanalTP\SamCoreBundle\Entity\UserApplicationRole $applicationRoles)
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
     * @param \CanalTP\SamCoreBundle\Entity\UserApplicationRole $roleParent
     * @return Role
     */
    public function addRoleGroupByApplication(\CanalTP\SamCoreBundle\Entity\UserApplicationRole $roleGroupByApplication)
    {
        $this->roleGroupByApplications[] = $roleGroupByApplication;

        return $this;
    }

    /**
     * Remove roleGroupByApplication
     *
     * @param \CanalTP\SamCoreBundle\Entity\Application $roleParent
     */
    public function removeRoleGroupByApplication(\CanalTP\SamCoreBundle\Entity\UserApplicationRole $roleGroupByApplication)
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
            $aRoles[] = $applicationRole->getRole()->getCanonicalName();
        }
        $this->setRoles($aRoles);
    }
}
