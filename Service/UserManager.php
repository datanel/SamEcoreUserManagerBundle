<?php

namespace CanalTP\SamEcoreUserManagerBundle\Service;

use CanalTP\SamEcoreApplicationManagerBundle\Component\BusinessComponentRegistry;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;

class UserManager extends BaseUserManager
{
    /**
     * @var Integer
     */
    private $users_limit = 20;

    /**
     * Business Registry
     * @var BusinessComponentRegistry
     */
    private $businessRegistry;

    protected $applications;

    /**
     * Set users_limit
     *
     * @return UserManager
     */
    public function setUsersLimit($users_limit)
    {
        $this->users_limit = $users_limit;

        return $this;
    }

    /**
     * Get users_limit
     *
     * @return Integer
     */
    public function getUsersLimit()
    {
        return $this->users_limit;
    }

    /**
     * Permet de récuperer tous les utilisateurs triés
     * par ordre de connexion antechronologique
     *
     * @return Array
     */
    public function findUsers()
    {
        $query = $this->repository->createQueryBuilder('u')
            ->orderBy('u.lastLogin', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Permet de récuperer tous les utilisateurs triés
     * par ordre de connexion antechronologique
     *
     * @param Integer $page
     *
     * @return Array
     */
    public function findPaginateUsers($page)
    {
        $offset = ($page-1)*$this->users_limit;
        $query = $this->repository->createQueryBuilder('u')
            ->setFirstResult($offset)
            ->setMaxResults($this->users_limit)
            ->orderBy('u.lastLogin', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Permet de récuperer le nombre d'utilisateurs
     *
     * @return Integer
     */
    public function countUsers()
    {
        $query = $this->repository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Permet de récuperer les utilisateurs qui ne contiennent pas
     * un role donné triés par ordre de connexion antechronologique
     *
     * @param Integer $page
     *
     * @return Array
     */
    public function findUsersExcludingRole($role)
    {
        $query = $this->repository->createQueryBuilder('u')
//            ->where('u.roles NOT LIKE :role')
            ->setParameter('role', '%'.$role.'%')
            ->orderBy('u.lastLogin', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Permet de récuperer les utilisateurs qui ne contiennent pas
     * un role donné triés par ordre de connexion antechronologique
     *
     * @param String $role
     * @param Integer $page
     *
     * @return Array
     */
    public function findPaginateUsersExcludingRole($role, $page)
    {
        $offset = ($page-1)*$this->users_limit;
        $query = $this->repository->createQueryBuilder('u')
//            ->where('u.roles NOT LIKE :role')
            ->setFirstResult($offset)
            ->setMaxResults($this->users_limit)
//            ->setParameter('role', '%'.$role.'%')
//            ->orderBy('u.lastLogin', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Permet de récuperer le nombre d'utilisateurs
     * qui ne contiennent pas un role donné
     *
     * @param String $role
     *
     * @return Integer
     */
    public function countUsersExcludingRole($role)
    {
        $query = $this->repository->createQueryBuilder('u')
            ->select('count(u.id)')
            // ->where('u.roles NOT LIKE :role')
            // ->setParameter('role', '%'.$role.'%')
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function findUser($user)
    {
        $query = $this->repository->createQueryBuilder('u')
            ->addSelect('r')
            ->addSelect('a')
            ->leftJoin('u.userRoles', 'r')
            ->leftJoin('r.application', 'a')
            ->where('u.id = :user')
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function deleteUser(UserInterface $user)
    {
        //get all user's application
        $roles = $user->getUserRoles();
        $appNames = array();

        foreach ($roles as $role) {
            $appNames[$role->getApplication()->getId()] = $role->getApplication()->getCanonicalName();
        }

        //remove all links between user and perimeters in all apps
        foreach ($appNames as $appName) {
            try{
                $this->getBusinessRegistry()
                    ->getBusinessComponent($appName)
                    ->getPerimetersManager()
                    ->deleteUserPerimeters($user);
            } catch (\Exception $e) {
            }
        }

        //finnaly, delete user with the external userManager
        parent::deleteUser($user);
    }

    public function setBusinessRegistry(BusinessComponentRegistry $businessRegistry)
    {
        $this->businessRegistry = $businessRegistry;
    }

    public function getBusinessRegistry()
    {
        return $this->businessRegistry;
    }

    // public function getApplications(UserInterface $user)
    // {
    //     if (null === $this->applications) {
    //         $apps = array();
    //         foreach ($user->getUserRoles() as $role) {
    //             $application = $role->getApplication();
    //             if (!isset($apps[$application->getId()])) {
    //                 try{
    //                     $apps[$application->getId()] = $role->getApplication();

    //                     $userPerimeters = $this->getBusinessRegistry()
    //                         ->getBusinessComponent($application->getCanonicalName())
    //                         ->getPerimetersManager()
    //                         ->getUserPerimeters($user);

    //                     $apps[$application->getId()]->setPerimeters($userPerimeters);
    //                 } catch (\Exception $e) {
    //                     $apps[$application->getId()]->setPerimeters(array());
    //                 }
    //             }

    //             //$apps[$application->getId()]->addRole($role);
    //         }
    //         $this->applications = $apps;
    //     }

    //     return $this->applications;
    // }
}
