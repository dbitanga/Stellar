<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Creates an account from the terminal, e.g. the first admin: bin/console app:create-user admin secret123 --admin */
#[AsCommand(name: 'app:create-user', description: 'Create an admin or staff account')]
class CreateUserCommand
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument] string $username,
        #[Argument] string $password,
        #[Option(description: 'Give the account the Admin role instead of Staff')] bool $admin = false,
    ): int {
        $user = (new User())
            ->setUsername($username)
            ->setRole($admin ? User::ROLE_ADMIN : User::ROLE_STAFF);
        $user->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Created %s account "%s".', $user->getRoleLabel(), $username));

        return Command::SUCCESS;
    }
}
