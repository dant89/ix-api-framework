<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractPermissionMigration;
use App\Security\Role\RoleFactory;

final class Version20220113152111 extends AbstractPermissionMigration
{
    protected function getPermissions(): array
    {
        return [
            RoleFactory::GET_PRODUCT_OFFERING,
            RoleFactory::GET_PRODUCT_OFFERINGS,
        ];
    }
}
