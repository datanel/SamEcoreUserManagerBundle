<?php

namespace CanalTP\SamEcoreUserManagerBundle\Doctrine;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;

class UserManager extends BaseUserManager
{
    /**
     * @var Integer
     */
    private $users_limit = 20;

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
}
