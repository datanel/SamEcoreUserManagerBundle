<?php

namespace CanalTP\SamEcoreUserManagerBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adding timezone column in public.t_user_usr table
 */
class Version001 extends AbstractMigration
{
    const VERSION = '0.0.1';

    public function getName()
    {
        return self::VERSION;
    }


    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE public.t_user_usr ADD COLUMN usr_timezone varchar(255) DEFAULT 'Europe/Paris'");
    }

    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE public.t_user_usr DROP COLUMN usr_timezone");
    }
}