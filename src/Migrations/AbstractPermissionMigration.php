<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;

abstract class AbstractPermissionMigration extends AbstractMigration
{
    protected const TABLE = 'permission';

    public function getDescription(): string
    {
        return 'Insert new permissions: ' . $this->listPermissions();
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema): void
    {
        $this->enforceMySql();
        foreach ($this->getPermissions() as $permission) {
            $this->addSql("INSERT INTO " . self::TABLE . " (permission) VALUES (:permission)", [
                'permission' => $permission,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema): void
    {
        $this->enforceMySql();
        foreach ($this->getPermissions() as $permission) {
            $this->addSql('DELETE FROM ' . self::TABLE . ' WHERE permission = :permission', [
                'permission' => $permission,
            ]);
        }
    }

    protected function listPermissions(): string
    {
        return implode(", ", $this->getPermissions());
    }

    abstract protected function getPermissions(): array;
}
