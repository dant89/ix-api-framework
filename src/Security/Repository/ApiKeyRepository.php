<?php

namespace App\Security\Repository;

use App\Security\Entity\ApiKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class ApiKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKey::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByApiKey(string $apiKey, string $secret): ?ApiKey
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.apiKey = :apiKey')
            ->andWhere('a.secret = :secret')
            ->setParameter('apiKey', $apiKey)
            ->setParameter('secret', $secret)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
