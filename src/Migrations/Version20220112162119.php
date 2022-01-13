<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112162119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create security key, permissions and token tables';
    }

    public function up(Schema $schema): void
    {
        $this->enforceMySql();

        # api_key
        $apiKeyTable = $schema->createTable('api_key');
        $apiKeyTable->addColumn('id', Types::INTEGER)
            ->setUnsigned(true)
            ->setAutoincrement(true);
        $apiKeyTable->addColumn('name', Types::STRING)
            ->setLength(50);
        $apiKeyTable->addColumn('api_key', Types::STRING)
            ->setLength(50);
        $apiKeyTable->addColumn('secret', Types::STRING)
            ->setLength(50);
        $apiKeyTable->addColumn('company_id', Types::STRING)
            ->setLength(36);
        $apiKeyTable->addColumn('created_at', Types::DATETIME_MUTABLE);
        $apiKeyTable->addUniqueIndex(['name'], 'api_name_unq');
        $apiKeyTable->addUniqueIndex(['api_key'], 'api_key_unq');
        $apiKeyTable->setPrimaryKey(['id'], 'id_idx');

        # api_key_permission
        $keyPermissionTable = $schema->createTable('api_key_permission');
        $keyPermissionTable->addColumn('api_key_id', Types::INTEGER)
            ->setUnsigned(true);
        $keyPermissionTable->addColumn('permission_id', Types::INTEGER)
            ->setUnsigned(true);
        $keyPermissionTable->setPrimaryKey(['api_key_id', 'permission_id'], 'api_key_permission_idx');

        # permission
        $permissionTable = $schema->createTable('permission');
        $permissionTable->addColumn('id', Types::INTEGER)
            ->setUnsigned(true)
            ->setAutoincrement(true);
        $permissionTable->addColumn('permission', Types::STRING)
            ->setLength(128);
        $permissionTable->addUniqueIndex(['permission'], 'permission_unq');
        $permissionTable->setPrimaryKey(['id'], 'id_idx');

        # refresh_token
        $refreshTokenTable = $schema->createTable('refresh_token');
        $refreshTokenTable->addColumn('id', Types::INTEGER)
            ->setUnsigned(true)
            ->setAutoincrement(true);
        $refreshTokenTable->addColumn('refresh_token', Types::STRING)
            ->setLength(128);
        $refreshTokenTable->addColumn('sub', Types::STRING)
            ->setLength(36);
        $refreshTokenTable->addColumn('valid', Types::DATETIME_MUTABLE);
        $refreshTokenTable->addColumn('api_key_id', Types::INTEGER)
            ->setUnsigned(true)
            ->setNotnull(true);
        $refreshTokenTable->addUniqueIndex(['refresh_token'], 'refresh_token_unq');
        $refreshTokenTable->addIndex(['valid'], 'valid_idx');
        $refreshTokenTable->addIndex(['api_key_id'], 'idx_api_key');
        $refreshTokenTable->setPrimaryKey(['id'], 'id_idx');
        $refreshTokenTable->addForeignKeyConstraint(
            $apiKeyTable,
            ['api_key_id'],
            ['id'],
            [
                'onDelete' => 'CASCADE'
            ],
            'fk_api_key'
        );

        # refresh_token_permission
        $refreshTokenPermissionTable = $schema->createTable('refresh_token_permission');
        $refreshTokenPermissionTable->addColumn('refresh_token_id', Types::INTEGER)
            ->setUnsigned(true)
            ->setNotnull(true);
        $refreshTokenPermissionTable->addColumn('permission_id', Types::INTEGER)
            ->setUnsigned(true)
            ->setNotnull(true);
        $refreshTokenPermissionTable->addIndex(['refresh_token_id'], 'idx_tok');
        $refreshTokenPermissionTable->addIndex(['permission_id'], 'idx_per');
        $refreshTokenPermissionTable->setPrimaryKey(['refresh_token_id', 'permission_id'], 'id_idx');
        $refreshTokenPermissionTable->addForeignKeyConstraint(
            $refreshTokenTable,
            ['refresh_token_id'],
            ['id'],
            [
                'onDelete' => 'CASCADE'
            ],
            'fk_token'
        );
        $refreshTokenPermissionTable->addForeignKeyConstraint(
            $permissionTable,
            ['permission_id'],
            ['id'],
            [
                'onDelete' => 'CASCADE'
            ],
            'fk_permission'
        );
    }

    public function down(Schema $schema): void
    {
        $this->enforceMySql();

        $refreshTokenPermissionTable = $schema->getTable('refresh_token_permission');
        $refreshTokenPermissionTable->removeForeignKey('fk_token');
        $refreshTokenPermissionTable->removeForeignKey('fk_permission');
        $schema->dropTable('refresh_token_permission');

        $refreshTokenTable = $schema->getTable('refresh_token');
        $refreshTokenTable->removeForeignKey('fk_api_key');
        $schema->dropTable('refresh_token');

        $schema->dropTable('permission');
        $schema->dropTable('api_key_permission');
        $schema->dropTable('api_key');
    }
}
