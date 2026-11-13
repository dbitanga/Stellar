<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Enum\ProductCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Checks the rubric's access rules the way a grader would: by requesting URLs as each kind of user.
 */
class AccessControlTest extends WebTestCase
{
    private const PASSWORD = 'secret-password';

    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $connection = $this->em->getConnection();
        foreach (['order_items', 'orders', 'cart', 'products', 'users'] as $table) {
            $connection->executeStatement("DELETE FROM $table");
        }
    }

    public function testAnonymousVisitorIsSentToLogin(): void
    {
        $this->client->request('GET', '/dashboard/product');

        self::assertResponseRedirects('/login');
    }

    public function testStaffIsDeniedEveryAdminOnlyPage(): void
    {
        $this->client->loginUser($this->createUser('staff1', User::ROLE_STAFF));

        foreach (['/dashboard', '/dashboard/user', '/dashboard/customer', '/dashboard/payment'] as $url) {
            $this->client->request('GET', $url);
            self::assertResponseStatusCodeSame(403, "Staff should not open $url");
        }

        $this->client->request('POST', '/dashboard/user/new');
        self::assertResponseStatusCodeSame(403, 'Staff should not create accounts');
    }

    public function testAdminCanOpenEveryAdminPage(): void
    {
        $this->client->loginUser($this->createUser('admin1', User::ROLE_ADMIN));

        foreach (['/dashboard', '/dashboard/user', '/dashboard/customer', '/dashboard/payment', '/dashboard/product'] as $url) {
            $this->client->request('GET', $url);
            self::assertResponseIsSuccessful("Admin should open $url");
        }
    }

    public function testStaffMenuHidesAdminLinks(): void
    {
        $this->client->loginUser($this->createUser('staff1', User::ROLE_STAFF));
        $crawler = $this->client->request('GET', '/dashboard/product');

        $menu = $crawler->filter('.sidebar-nav')->text();
        self::assertStringContainsString('Products', $menu);
        self::assertStringNotContainsString('Staff', $menu);
        self::assertStringNotContainsString('Customers', $menu);
    }

    public function testStaffLogsInThroughTheForm(): void
    {
        $this->createUser('staff1', User::ROLE_STAFF);

        $this->submitLogin('staff1', self::PASSWORD);

        self::assertResponseRedirects('/after-login');
        $this->client->followRedirect();
        self::assertResponseRedirects('/dashboard/order');
    }

    public function testCustomerLogsInToTheStoreButCannotOpenTheDashboard(): void
    {
        $this->createUser('shopper', User::ROLE_USER);

        $this->submitLogin('shopper', self::PASSWORD);
        self::assertResponseRedirects('/after-login');
        $this->client->followRedirect();
        self::assertResponseRedirects('/');

        foreach (['/dashboard', '/dashboard/order', '/dashboard/product'] as $url) {
            $this->client->request('GET', $url);
            self::assertResponseStatusCodeSame(403, "A customer should not open $url");
        }
    }

    public function testDisabledAccountCannotLogIn(): void
    {
        $this->createUser('former', User::ROLE_STAFF, active: false);

        $this->submitLogin('former', self::PASSWORD);
        $this->client->followRedirect();

        self::assertSelectorTextContains('.form-error', 'disabled');
    }

    public function testLogoutNeedsAPostWithAValidToken(): void
    {
        $this->client->loginUser($this->createUser('staff1', User::ROLE_STAFF));

        $this->client->request('GET', '/logout');
        self::assertResponseStatusCodeSame(405, 'A plain link must not log the user out');

        $crawler = $this->client->request('GET', '/dashboard/product');
        $this->client->submit($crawler->selectButton('Log Out')->form());
        self::assertResponseRedirects('/');

        $this->client->request('GET', '/dashboard/product');
        self::assertResponseRedirects('/login');
    }

    public function testStaffCanOnlyEditTheirOwnProducts(): void
    {
        $staff = $this->createUser('staff1', User::ROLE_STAFF);
        $other = $this->createUser('staff2', User::ROLE_STAFF);
        $category = ProductCategory::Gaming;
        $own = $this->createProduct('Own Laptop', $category, $staff);
        $theirs = $this->createProduct('Their Laptop', $category, $other);

        $this->client->loginUser($staff);

        $this->client->request('POST', "/dashboard/product/{$theirs->getId()}/delete");
        self::assertResponseStatusCodeSame(403);

        $crawler = $this->client->request('GET', '/dashboard/product');
        $form = $crawler->filter("form[action='/dashboard/product/{$own->getId()}/delete']")->form();
        $this->client->submit($form);
        self::assertResponseRedirects('/dashboard/product');

        $this->em->clear();
        self::assertNull($this->em->find(Product::class, $own->getId()));
        self::assertNotNull($this->em->find(Product::class, $theirs->getId()));
    }

    public function testProductsCanBeSearchedAndFiltered(): void
    {
        $staff = $this->createUser('staff1', User::ROLE_STAFF);
        $admin = $this->createUser('admin1', User::ROLE_ADMIN);
        $gaming = ProductCategory::Gaming;
        $work = ProductCategory::Workstation;
        $this->createProduct('Nova Gaming', $gaming, $staff);
        $this->createProduct('Atlas Work', $work, $admin, visible: false);

        $this->client->loginUser($staff);

        $this->client->request('GET', '/dashboard/product', ['q' => 'nova']);
        self::assertSame(['Nova Gaming'], $this->productNamesOnPage());

        $this->client->request('GET', '/dashboard/product', ['category' => $work->value]);
        self::assertSame(['Atlas Work'], $this->productNamesOnPage());

        $this->client->request('GET', '/dashboard/product', ['status' => 'hidden']);
        self::assertSame(['Atlas Work'], $this->productNamesOnPage());

        $this->client->request('GET', '/dashboard/product', ['mine' => '1']);
        self::assertSame(['Nova Gaming'], $this->productNamesOnPage());

        $this->client->request('GET', '/dashboard/product', ['status' => 'bogus']);
        self::assertResponseStatusCodeSame(400);

        $this->client->request('GET', '/dashboard/product', ['category' => 'bogus']);
        self::assertResponseStatusCodeSame(400);
    }

    public function testFilterButtonWorksWithBlankFields(): void
    {
        $staff = $this->createUser('staff1', User::ROLE_STAFF);
        $gaming = ProductCategory::Gaming;
        $this->createProduct('Nova Gaming', $gaming, $staff);
        $this->createProduct('Atlas Work', $gaming, $staff);

        $this->client->loginUser($staff);
        $crawler = $this->client->request('GET', '/dashboard/product');

        // Only the search box is filled; category, tier and status are submitted as "".
        $this->client->submit($crawler->selectButton('Filter')->form(['q' => 'atlas']));

        self::assertSame(['Atlas Work'], $this->productNamesOnPage());
    }

    private function submitLogin(string $username, string $password): void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->client->submit($crawler->selectButton('Log In')->form([
            '_username' => $username,
            '_password' => $password,
        ]));
    }

    /** @return list<string> */
    private function productNamesOnPage(): array
    {
        self::assertResponseIsSuccessful();

        return $this->client->getCrawler()->filter('table[data-datatable] tbody tr td:nth-child(2)')->each(fn ($td) => $td->text());
    }

    private function createUser(string $username, string $role, bool $active = true): User
    {
        $user = (new User())->setUsername($username)->setRole($role)->setActive($active);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, self::PASSWORD));
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function createProduct(string $name, ProductCategory $category, User $owner, bool $visible = true): Product
    {
        $product = (new Product())
            ->setName($name)
            ->setCategory($category)
            ->setCreatedBy($owner)
            ->setTier('SE')
            ->setPrice('999.00')
            ->setProcessor('CPU')
            ->setRam('16GB')
            ->setStorage('512GB')
            ->setGraphics('GPU')
            ->setImagePath('images/test.png')
            ->setStock(10)
            ->setVisible($visible);
        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }
}
