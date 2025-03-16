<?php

namespace App\Service;
use Doctrine\ORM\EntityManagerInterface;

class PaginationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function paginate(string $entityClass, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $repository = $this->entityManager->getRepository($entityClass);
        $query = $repository->createQueryBuilder('e')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        return $query->getResult();
    }


}