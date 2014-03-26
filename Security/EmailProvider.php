<?php

namespace CanalTP\SamEcoreUserManagerBundle\Security;

use FOS\UserBundle\Security\UserProvider;

class EmailProvider extends UserProvider
{
    /**
     * {@inheritDoc}
     */
    protected function findUser($email)
    {
        return $this->userManager->findUserByEmail($email);
    }
}
