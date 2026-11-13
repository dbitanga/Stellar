<?php

namespace App\Tests\Controller;

use App\Dto\Checkout;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\ProductCategory;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** The return flow: the customer asks for a return after delivery, then staff review it. */
class OrderRefundTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private OrderService $orders;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->orders = static::getContainer()->get(OrderService::class);

        foreach (['order_items', 'orders', 'cart', 'products', 'users'] as $table) {
            $this->em->getConnection()->executeStatement("DELETE FROM $table");
        }
    }

    public function testCustomerRequestsAReturnFromTheirAccountPage(): void
    {
        $order = $this->deliveredOrder(quantity: 2, stockBefore: 10, returnReason: null);
        self::assertSame(Order::PAYMENT_PAID, $order->getPaymentStatus(), 'COD is collected on delivery, so there is something to refund');

        $this->client->loginUser($order->getUser());
        $crawler = $this->client->request('GET', '/account');
        $this->client->submit($crawler->selectButton('Return')->form(['reason' => 'Defective']));

        self::assertResponseRedirects('/account');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.store-flash-success', "Return requested for order #{$order->getId()}");

        $this->em->clear();
        $order = $this->em->find(Order::class, $order->getId());
        self::assertSame(Order::STATUS_RETURN_REQUESTED, $order->getOrderStatus());
        self::assertSame('Defective', $order->getReturnReason());
        self::assertNotNull($order->getReturnRequestedAt());
    }

    public function testCustomerCannotRequestAReturnForSomeoneElsesOrder(): void
    {
        $order = $this->deliveredOrder(quantity: 1, stockBefore: 5, returnReason: null);
        $this->client->loginUser($this->user('other', User::ROLE_USER));

        $this->client->request('POST', "/account/order/{$order->getId()}/return", ['reason' => 'Defective']);
        self::assertResponseStatusCodeSame(404);
    }

    public function testOrderWithoutReturnStaysDeliveredAndHasNoRefundButton(): void
    {
        $order = $this->deliveredOrder(quantity: 1, stockBefore: 5, returnReason: null);
        self::assertSame(Order::STATUS_DELIVERED, $order->getOrderStatus());

        $this->client->loginUser($this->user('admin1', User::ROLE_ADMIN));
        $crawler = $this->client->request('GET', "/dashboard/order/{$order->getId()}");
        self::assertCount(0, $crawler->selectButton('Approve & refund'));
    }

    public function testAdminApprovesAReturnAndRefunds(): void
    {
        $order = $this->deliveredOrder(quantity: 2, stockBefore: 10);
        $productId = $order->getItems()->first()->getProduct()->getId();
        self::assertSame(8, $this->em->find(Product::class, $productId)->getStock());

        $this->client->loginUser($this->user('admin1', User::ROLE_ADMIN));
        $crawler = $this->client->request('GET', "/dashboard/order/{$order->getId()}");
        self::assertSelectorTextContains('.alert-warning', 'Defective');
        $this->client->submit($crawler->selectButton('Approve & refund')->form());

        self::assertResponseRedirects("/dashboard/order/{$order->getId()}");
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'returned and refunded');

        $this->em->clear();
        $order = $this->em->find(Order::class, $order->getId());
        self::assertSame(Order::STATUS_RETURNED, $order->getOrderStatus());
        self::assertSame(Order::PAYMENT_REFUNDED, $order->getPaymentStatus());
        self::assertSame(10, $this->em->find(Product::class, $productId)->getStock());
    }

    public function testStaffCanRejectButNotRefund(): void
    {
        $order = $this->deliveredOrder(quantity: 1, stockBefore: 5);

        $this->client->loginUser($this->user('staff1', User::ROLE_STAFF));
        $crawler = $this->client->request('GET', "/dashboard/order/{$order->getId()}");
        self::assertCount(0, $crawler->selectButton('Approve & refund'), 'Staff should not see the refund button');

        $this->client->request('POST', "/dashboard/order/{$order->getId()}/refund");
        self::assertResponseStatusCodeSame(403);

        $crawler = $this->client->request('GET', "/dashboard/order/{$order->getId()}");
        $this->client->submit($crawler->selectButton('Reject return')->form());
        self::assertResponseRedirects("/dashboard/order/{$order->getId()}");

        $this->em->clear();
        $order = $this->em->find(Order::class, $order->getId());
        self::assertSame(Order::STATUS_DELIVERED, $order->getOrderStatus());
        self::assertSame(Order::PAYMENT_PAID, $order->getPaymentStatus());
    }

    public function testOnlyRequestedReturnsCanBeRefunded(): void
    {
        $order = $this->deliveredOrder(quantity: 1, stockBefore: 5);
        $this->orders->refund($order);

        $this->expectException(\DomainException::class);
        $this->orders->refund($order);
    }

    private function deliveredOrder(int $quantity, int $stockBefore, ?string $returnReason = 'Defective'): Order
    {
        $product = $this->product($stockBefore);
        $customer = $this->customer();
        $order = $this->orders->place($customer, [$product->getId() => $quantity], Checkout::forCustomer($customer));
        $this->orders->ship($order);
        $this->orders->deliver($order); // COD becomes Paid on delivery
        if (null !== $returnReason) {
            $this->orders->requestReturn($order, $returnReason);
        }

        return $order;
    }

    private function product(int $stock): Product
    {
        $product = (new Product())
            ->setName('Nova')->setCategory(ProductCategory::Gaming)->setTier('SE')->setPrice('1000.00')
            ->setProcessor('CPU')->setRam('16GB')->setStorage('512GB')->setGraphics('GPU')
            ->setImagePath('images/test.png')->setStock($stock);
        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    private function customer(): User
    {
        $customer = $this->user('shopper', User::ROLE_USER)
            ->setMobileNumber('09170000000')
            ->setDeliveryAddress('123 Test St');
        $this->em->flush();

        return $customer;
    }

    private function user(string $username, string $role): User
    {
        $user = (new User())->setUsername($username)->setRole($role)->setPassword('not-used');
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
