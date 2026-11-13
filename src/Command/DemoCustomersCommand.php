<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Adds a few customer accounts so orders can be simulated without the storefront. Safe to run twice. */
#[AsCommand(name: 'app:demo-customers', description: 'Create demo customer accounts (password: customer123)')]
class DemoCustomersCommand
{
    private const CUSTOMERS = [
        ['juan', 'Juan', 'Dela Cruz', 'juan@example.com', '09171234567', '12 Osmeña Blvd, Cebu City, Cebu'],
        ['maria', 'Maria', 'Santos', 'maria@example.com', '09181234567', '45 Gorordo Ave, Lahug, Cebu City, Cebu'],
        ['jose', 'Jose', 'Reyes', 'jose@example.com', '09191234567', '8 A.S. Fortuna St, Mandaue City, Cebu'],
        ['ana', 'Ana', 'Villanueva', 'ana@example.com', '09201234567', '230 M.L. Quezon Hwy, Lapu-Lapu City, Cebu'],
        ['carlo', 'Carlo', 'Garcia', 'carlo@example.com', '09211234567', '17 Natalio Bacalso Ave, Talisay City, Cebu'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $users,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $created = 0;
        foreach (self::CUSTOMERS as [$username, $first, $last, $email, $mobile, $address]) {
            if ($this->users->findOneBy(['username' => $username])) {
                continue;
            }

            $user = (new User())
                ->setUsername($username)
                ->setRole(User::ROLE_USER)
                ->setFirstName($first)
                ->setLastName($last)
                ->setEmail($email)
                ->setMobileNumber($mobile)
                ->setDeliveryAddress($address);
            $user->setPassword($this->hasher->hashPassword($user, 'customer123'));
            $this->em->persist($user);
            ++$created;
        }

        $this->em->flush();
        $io->success(sprintf('Created %d demo customer(s).', $created));

        return Command::SUCCESS;
    }
}
