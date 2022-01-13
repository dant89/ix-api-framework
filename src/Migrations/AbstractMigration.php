<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\Migrations\AbstractMigration as BaseAbstractMigration;

abstract class AbstractMigration extends BaseAbstractMigration
{
    public function enforceMySql()
    {
        try {
            $isMySql = $this->connection->getDatabasePlatform()->getName() === 'mysql';
        } catch (DBALException $e) {
            $isMySql = false;
        }
        $this->abortIf(
            !$isMySql,
            'Migration can only be executed safely on \'mysql\'.'
        );
    }
}
