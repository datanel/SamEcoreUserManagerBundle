<?php

namespace CanalTP\SamEcoreUserManagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use CanalTP\SamEcoreUserManagerBundle\DependencyInjection\CanalTPSamEcoreUserManagerExtension;

class CanalTPSamEcoreUserManagerBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }

    public function getContainerExtension()
    {
        return new CanalTPSamEcoreUserManagerExtension();
    }
}
