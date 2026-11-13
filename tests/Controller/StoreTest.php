<?php

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\ProductCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** The customer side: sign up, browse, add to cart, check out, and see the order in the account. */
class StoreTest extends WebTestCase
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

    public function testStorePagesArePublic(): void
    {
        foreach (['/', '/products', '/login', '/signup'] as $url) {
            $this->client->request('GET', $url);
            self::assertResponseIsSuccessful("$url should be public");
        }
    }

    public function testCartAndAccountNeedLogin(): void
    {
        foreach (['/cart', '/account'] as $url) {
            $this->client->request('GET', $url);
            self::assertResponseRedirects('/login');
        }
    }

    public function testVisitorSignsUpAsACustomerAndCanLogIn(): void
    {
        $crawler = $this->client->request('GET', '/signup');
        $this->client->submit($crawler->selectButton('Sign Up')->form([
            'registration[firstName]' => 'Juan',
            'registration[lastName]' => 'Dela Cruz',
            'registration[username]' => 'juan',
            'registration[email]' => 'juan@example.com',
            'registration[mobileNumber]' => '09171234567',
            'registration[deliveryAddress]' => '12 Osmeña Blvd, Cebu City',
            'registration[plainPassword][first]' => 'stellar123',
            'registration[plainPassword][second]' => 'stellar123',
        ]));

        self::assertResponseRedirects('/signup/done');
        $this->client->followRedirect();
        self::assertSelectorTextContains('main', 'Welcome, juan!');

        $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'juan']);
        self::assertSame(User::ROLE_USER, $user->getRole());
        self::assertNotSame('stellar123', $user->getPassword(), 'The password must be hashed');

        $crawler = $this->client->request('GET', '/login');
        $this->client->submit($crawler->selectButton('Log In')->form(['_username' => 'juan', '_password' => 'stellar123']));
        $this->client->followRedirect();
        self::assertResponseRedirects('/');
    }

    public function testSignupRejectsAWeakPassword(): void
    {
        $crawler = $this->client->request('GET', '/signup');
        $this->client->submit($crawler->selectButton('Sign Up')->form([
            'registration[firstName]' => 'Juan',
            'registration[lastName]' => 'Dela Cruz',
            'registration[username]' => 'juan',
            'registration[email]' => 'juan@example.com',
            'registration[mobileNumber]' => '09171234567',
            'registration[deliveryAddress]' => 'Cebu City',
            'registration[plainPassword][first]' => 'PASSWORDONLY',
            'registration[plainPassword][second]' => 'PASSWORDONLY',
        ]));

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.form-error', 'lowercase letter');
        self::assertNull($this->em->getRepository(User::class)->findOneBy(['username' => 'juan']));
    }

    public function testProductsPageShowsOnlyVisibleProducts(): void
    {
        $this->product('Nova SE', stock: 5);
        $this->product('Secret Prototype', stock: 5, visible: false);

        $crawler = $this->client->request('GET', '/products');

        self::assertSelectorTextContains('#gaming', 'Nova SE');
        self::assertStringNotContainsString('Secret Prototype', $crawler->filter('main')->text());
    }

    public function testCustomerAddsToCartAndChecksOut(): void
    {
        $product = $this->product('Nova SE', stock: 5, price: '1000.00');
        $customer = $this->customer();
        $this->client->loginUser($customer);

        // Add twice the way the products page does (fetch, JSON answer).
        $crawler = $this->client->request('GET', '/products');
        $form = $crawler->filter('form.add-to-cart-form')->form();
        foreach ([1, 2] as $_) {
            $this->client->request('POST', $form->getUri(), $form->getValues(), [], ['HTTP_ACCEPT' => 'application/json']);
            self::assertResponseIsSuccessful();
            self::assertTrue(json_decode($this->client->getResponse()->getContent(), true)['success']);
        }

        // Subtotal 2,000 + tax 200 + protection 40 + shipping (250 + 2 × 100) = 2,690.
        $crawler = $this->client->request('GET', '/cart');
        self::assertSelectorTextContains('.cart-summary-card .grand-total', '₱2,690.00');
        self::assertSelectorTextContains('.qty-display', '2');

        $this->client->submit($crawler->selectButton('Confirm Order')->form([
            'checkout[paymentMethod]' => Order::PAYMENT_COD,
        ]));
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertSelectorTextContains('main', 'Order Confirmed!');

        $this->em->clear();
        $order = $this->em->getRepository(Order::class)->findOneBy([]);
        self::assertSame('2690.00', $order->getTotalAmount());
        self::assertSame(Order::PAYMENT_PENDING, $order->getPaymentStatus());
        self::assertSame('Juan Dela Cruz', $order->getShippingName(), 'Delivery details come from the profile');
        self::assertSame(3, $this->em->find(Product::class, $product->getId())->getStock());
        self::assertSame(0, $this->em->getRepository(Cart::class)->count([]), 'The cart is emptied');

        // The order shows up in the customer's account and in the dashboard.
        $this->client->request('GET', '/account');
        self::assertSelectorTextContains('.orders-list', "Order #{$order->getId()}");
    }

    public function testCannotAddMoreThanInStock(): void
    {
        $product = $this->product('Nova SE', stock: 1);
        $this->client->loginUser($this->customer());

        $crawler = $this->client->request('GET', '/products');
        $form = $crawler->filter('form.add-to-cart-form')->form();
        $this->client->request('POST', $form->getUri(), $form->getValues(), [], ['HTTP_ACCEPT' => 'application/json']);
        $this->client->request('POST', $form->getUri(), $form->getValues(), [], ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseStatusCodeSame(409);
        self::assertStringContainsString('Only 1 of Nova SE left', $this->client->getResponse()->getContent());
        self::assertSame(1, $this->em->getRepository(Cart::class)->findOneBy(['product' => $product->getId()])->getQuantity());
    }

    public function testCartChangesNeedAValidToken(): void
    {
        $product = $this->product('Nova SE', stock: 5);
        $this->client->loginUser($this->customer());

        $this->client->request('POST', "/cart/add/{$product->getId()}", ['_token' => 'forged']);

        // Symfony treats a bad CSRF token as a failed authentication and sends the request to the login page.
        self::assertResponseRedirects('/login');
        self::assertSame(0, $this->em->getRepository(Cart::class)->count([]));
    }

    private function product(string $name, int $stock, string $price = '1000.00', bool $visible = true): Product
    {
        $product = (new Product())
            ->setName($name)->setCategory(ProductCategory::Gaming)->setTier('SE')->setPrice($price)
            ->setProcessor('CPU')->setRam('16GB')->setStorage('512GB')->setGraphics('GPU')
            ->setImagePath('images/products/Gaming.png')->setStock($stock)->setVisible($visible);
        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    private function customer(): User
    {
        $customer = (new User())
            ->setUsername('shopper')->setRole(User::ROLE_USER)->setPassword('not-used')
            ->setFirstName('Juan')->setLastName('Dela Cruz')
            ->setMobileNumber('09170000000')->setDeliveryAddress('123 Test St');
        $this->em->persist($customer);
        $this->em->flush();

        return $customer;
    }
}
