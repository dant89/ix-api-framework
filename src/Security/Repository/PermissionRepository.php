<?php

namespace App\Security\Repository;

use App\Security\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByPermission(string $permission): ?Permission
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.permission = :permission')
            ->setParameter('permission', $permission)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
