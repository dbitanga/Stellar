<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        foreach (['order_items', 'orders', 'cart', 'products', 'users'] as $table) {
            $this->em->getConnection()->executeStatement("DELETE FROM $table");
        }
    }

    public function testStaffCanEditOwnProfile(): void
    {
        $staff = $this->createUser('staff1', User::ROLE_STAFF, 'old@example.com');
        $this->client->loginUser($staff);

        $crawler = $this->client->request('GET', '/dashboard/profile');
        $this->client->submit($crawler->selectButton('Save changes')->form([
            'profile[firstName]' => 'Juan',
            'profile[lastName]' => 'Dela Cruz',
            'profile[email]' => 'juan@example.com',
            'profile[mobileNumber]' => '09171234567',
        ]));

        self::assertResponseRedirects('/dashboard/profile');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Your profile has been updated.');

        $saved = $this->reload($staff);
        self::assertSame('Juan', $saved->getFirstName());
        self::assertSame('Dela Cruz', $saved->getLastName());
        self::assertSame('juan@example.com', $saved->getEmail());
        self::assertSame('09171234567', $saved->getMobileNumber());
        self::assertSame('staff1', $saved->getUsername());
    }

    public function testStaffCannotPromoteThemselvesThroughTheProfileForm(): void
    {
        $staff = $this->createUser('staff1', User::ROLE_STAFF, 'staff@example.com');
        $this->client->loginUser($staff);

        $crawler = $this->client->request('GET', '/dashboard/profile');
        $token = $crawler->filter('input[name="profile[_token]"]')->attr('value');

        // A tampered request that adds fields the form does not offer.
        $this->client->request('POST', '/dashboard/profile/edit', ['profile' => [
            '_token' => $token,
            'firstName' => 'Sneaky',
            'role' => User::ROLE_ADMIN,
            'username' => 'admin',
        ]]);

        self::assertResponseRedirects('/dashboard/profile');
        $saved = $this->reload($staff);
        self::assertSame(User::ROLE_STAFF, $saved->getRole());
        self::assertSame('staff1', $saved->getUsername());
        self::assertNotSame('Sneaky', $saved->getFirstName());
    }

    public function testEmailMustBeUnique(): void
    {
        $this->createUser('other', User::ROLE_STAFF, 'taken@example.com');
        $staff = $this->createUser('staff1', User::ROLE_STAFF, 'mine@example.com');
        $this->client->loginUser($staff);

        $crawler = $this->client->request('GET', '/dashboard/profile');
        $this->client->submit($crawler->selectButton('Save changes')->form([
            'profile[email]' => 'taken@example.com',
        ]));

        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'That email is already used.');
        self::assertSame('mine@example.com', $this->reload($staff)->getEmail());
    }

    public function testProfileEditRequiresLogin(): void
    {
        $this->client->request('POST', '/dashboard/profile/edit');

        self::assertResponseRedirects('/login');
    }

    private function createUser(string $username, string $role, string $email): User
    {
        $user = (new User())->setUsername($username)->setRole($role)->setEmail($email)->setPassword('not-used');
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function reload(User $user): User
    {
        $this->em->clear();

        return $this->em->find(User::class, $user->getId());
    }
}
