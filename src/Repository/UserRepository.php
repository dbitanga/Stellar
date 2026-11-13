<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function countByRole(string $role): int
    {
        return $this->count(['role' => $role]);
    }

    /** @return User[] staff and admin accounts */
    public function findStaff(): array
    {
        return $this->findBy(['role' => [User::ROLE_STAFF, User::ROLE_ADMIN]], ['id' => 'ASC']);
    }

    /**
     * Customers with how many orders they placed and how much they have paid.
     *
     * @return list<array{0: User, orderCount: int, totalSpent: string}>
     */
    public function findCustomersWithStats(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->addSelect('COUNT(o.id) AS orderCount')
            ->addSelect('COALESCE(SUM(CASE WHEN o.paymentStatus = :paid THEN o.totalAmount ELSE 0 END), 0) AS totalSpent')
            ->leftJoin(Order::class, 'o', 'WITH', 'o.user = u')
            ->andWhere('u.role = :role')
            ->setParameter('role', User::ROLE_USER)
            ->setParameter('paid', Order::PAYMENT_PAID)
            ->groupBy('u.id')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
