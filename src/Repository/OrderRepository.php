<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Order> */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /** @return Order[] newest first, with customer and items loaded */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('u', 'i')
            ->join('o.user', 'u')
            ->leftJoin('o.items', 'i')
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Order[] newest first, with customer loaded */
    public function findRecent(int $limit): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('u')
            ->join('o.user', 'u')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** Sum of order totals with this payment status, leaving out cancelled orders unless they were refunded. */
    public function sumByPaymentStatus(string $paymentStatus): string
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COALESCE(SUM(o.totalAmount), 0)')
            ->andWhere('o.paymentStatus = :payment')
            ->setParameter('payment', $paymentStatus);

        if (Order::PAYMENT_REFUNDED !== $paymentStatus) {
            $qb->andWhere('o.orderStatus != :cancelled')->setParameter('cancelled', Order::STATUS_CANCELLED);
        }

        return (string) $qb->getQuery()->getSingleScalarResult();
    }
}
