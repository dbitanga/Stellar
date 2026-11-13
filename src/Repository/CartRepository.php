<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Cart> */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    /** @return Cart[] the customer's cart lines with their products, oldest first */
    public function findForUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('p')
            ->join('c.product', 'p')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
