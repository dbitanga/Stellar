<?php

namespace App\Repository;

use App\Dto\ProductFilter;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\ProductCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Product> */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /** @return Product[] */
    public function findFiltered(ProductFilter $filter, ?User $currentUser = null, int $lowStockThreshold = 5): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('u')
            ->leftJoin('p.createdBy', 'u')
            ->orderBy('p.id', 'ASC');

        if (null !== $filter->q && '' !== trim($filter->q)) {
            $qb->andWhere('p.name LIKE :q OR p.processor LIKE :q OR p.graphics LIKE :q OR p.category LIKE :q')
                ->setParameter('q', '%'.addcslashes(trim($filter->q), '%_').'%');
        }

        if (null !== $filter->category) {
            $qb->andWhere('p.category = :category')->setParameter('category', ProductCategory::from($filter->category));
        }

        if (null !== $filter->tier) {
            $qb->andWhere('p.tier = :tier')->setParameter('tier', $filter->tier);
        }

        match ($filter->status) {
            'visible' => $qb->andWhere('p.visible = true'),
            'hidden' => $qb->andWhere('p.visible = false'),
            'low_stock' => $qb->andWhere('p.stock <= :threshold')->setParameter('threshold', $lowStockThreshold),
            default => null,
        };

        if ($filter->mine && null !== $currentUser) {
            $qb->andWhere('p.createdBy = :me')->setParameter('me', $currentUser);
        }

        return $qb->getQuery()->getResult();
    }

    /** @return Product[] products a customer could buy right now */
    public function findAvailable(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.visible = true')
            ->andWhere('p.stock > 0')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Product[] */
    public function findLowStock(int $threshold = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stock <= :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countLowStock(int $threshold = 5): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.stock <= :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getSingleScalarResult();
    }
}