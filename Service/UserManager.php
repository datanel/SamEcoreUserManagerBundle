<?php

namespace CanalTP\SamEcoreUserManagerBundle\Service;

/**
 * Description of UserManager
 * A FOS UserManager facade
 *
 * @author Kévin ZIEMIANSKI <kevin.ziemianski@canaltp.fr>
 */
class UserManager
{
    protected $fosUserManager;
    protected $samBusinessComponent;
    
    public function __construct($fosUserManager, $samBusinessComponent) 
    {
        $this->fosUserManager = $fosUserManager;
        $this->samBusinessComponent = $samBusinessComponent;
    }
    
    public function deleteUser($user)
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
                $this->samBusinessComponent
                    ->getBusinessComponent($appName)
                    ->getPerimetersManager()
                    ->deleteUserPerimeters($user);
            } catch (\Exception $e) {
            }
        }
        
        //finnaly, delete user with the external userManager
        $this->fosUserManager->deleteUser($user);
    }
    
    public function findUserBy($condition)
    {
        return $this->fosUserManager->findUserBy($condition);
    }
}
