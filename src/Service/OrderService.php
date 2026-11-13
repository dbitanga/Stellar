<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Places orders and moves them through their statuses.
 * Throws \DomainException with a user-facing message when an action isn't allowed.
 */
class OrderService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Works like a store checkout: copies current prices, takes the quantities out of stock,
     * and marks card payments as paid straight away (COD stays pending until delivery).
     *
     * @param array<int, int> $quantities   product id => quantity
     * @param string|null     $returnReason when set, the customer asks to return the order as soon as it is delivered
     */
    public function place(User $customer, array $quantities, string $paymentMethod, ?string $returnReason = null): Order
    {
        if (null !== $returnReason && !\in_array($returnReason, Order::RETURN_REASONS, true)) {
            throw new \DomainException('Choose a valid return reason.');
        }
        $quantities = array_filter($quantities, static fn (?int $qty): bool => $qty > 0);
        if (!$quantities) {
            throw new \DomainException('Choose at least one product.');
        }
        if (!\in_array($paymentMethod, Order::PAYMENT_METHODS, true)) {
            throw new \DomainException('Choose a payment method.');
        }
        if (!$customer->getMobileNumber() || !$customer->getDeliveryAddress()) {
            throw new \DomainException(sprintf('%s has no mobile number or delivery address.', $customer->getUsername()));
        }

        $order = (new Order())
            ->setUser($customer)
            ->setPaymentMethod($paymentMethod)
            ->setReturnReason($returnReason)
            ->setPaymentStatus(Order::PAYMENT_CARD === $paymentMethod ? Order::PAYMENT_PAID : Order::PAYMENT_PENDING)
            ->setShippingName($customer->getFullName() ?: (string) $customer->getUsername())
            ->setShippingMobile($customer->getMobileNumber())
            ->setShippingAddress($customer->getDeliveryAddress());

        $this->em->beginTransaction();
        try {
            $total = '0.00';
            foreach ($quantities as $productId => $qty) {
                // Row lock: two orders at once can't both take the last units.
                $product = $this->em->find(Product::class, $productId, LockMode::PESSIMISTIC_WRITE);
                if (!$product || !$product->isVisible()) {
                    throw new \DomainException('One of the chosen products is no longer available.');
                }
                if ($product->getStock() < $qty) {
                    throw new \DomainException(sprintf('Only %d of %s left in stock.', $product->getStock(), $product->getName()));
                }

                $product->setStock($product->getStock() - $qty);
                $order->addItem((new OrderItem())
                    ->setProduct($product)
                    ->setPriceAtPurchase($product->getPrice())
                    ->setQuantity($qty));
                $total = bcadd($total, bcmul((string) $product->getPrice(), (string) $qty, 2), 2);
            }

            $order->setTotalAmount($total);
            $this->em->persist($order);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        return $order;
    }

    public function ship(Order $order): void
    {
        $this->requireStatus($order, Order::STATUS_PROCESSING, 'shipped');
        $order->setOrderStatus(Order::STATUS_SHIPPED);
        $this->em->flush();
    }

    /**
     * Cash on delivery is collected when the order arrives. If the customer planned a return,
     * the order goes straight to "Return requested" for staff to review.
     */
    public function deliver(Order $order): void
    {
        $this->requireStatus($order, Order::STATUS_SHIPPED, 'delivered');
        $order->setOrderStatus(Order::STATUS_DELIVERED);
        if (Order::PAYMENT_PENDING === $order->getPaymentStatus()) {
            $order->setPaymentStatus(Order::PAYMENT_PAID);
        }
        if (null !== $order->getReturnReason()) {
            $order->setOrderStatus(Order::STATUS_RETURN_REQUESTED);
            $order->setReturnRequestedAt(new \DateTimeImmutable());
        }
        $this->em->flush();
    }

    /** Puts the stock back, and refunds the order if it was already paid. */
    public function cancel(Order $order): void
    {
        $this->requireStatus($order, Order::STATUS_PROCESSING, 'cancelled');
        $this->restock($order);
        $order->setOrderStatus(Order::STATUS_CANCELLED);
        if (Order::PAYMENT_PAID === $order->getPaymentStatus()) {
            $order->setPaymentStatus(Order::PAYMENT_REFUNDED);
        }
        $this->em->flush();
    }

    /** Approves the customer's return: refund the payment and put the items back into stock. */
    public function refund(Order $order): void
    {
        $this->requireStatus($order, Order::STATUS_RETURN_REQUESTED, 'refunded');
        if (Order::PAYMENT_PAID !== $order->getPaymentStatus()) {
            throw new \DomainException(sprintf('Order #%d has not been paid, so there is nothing to refund.', $order->getId()));
        }
        $this->restock($order);
        $order->setOrderStatus(Order::STATUS_RETURNED);
        $order->setPaymentStatus(Order::PAYMENT_REFUNDED);
        $this->em->flush();
    }

    /** Turns down the customer's return: the order stays delivered and the payment is kept. */
    public function rejectReturn(Order $order): void
    {
        $this->requireStatus($order, Order::STATUS_RETURN_REQUESTED, 'rejected');
        $order->setOrderStatus(Order::STATUS_DELIVERED);
        $this->em->flush();
    }

    private function restock(Order $order): void
    {
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $product->setStock($product->getStock() + $item->getQuantity());
        }
    }

    public function markPaid(Order $order): void
    {
        if (Order::STATUS_CANCELLED === $order->getOrderStatus() || Order::PAYMENT_PENDING !== $order->getPaymentStatus()) {
            throw new \DomainException(sprintf('Order #%d is not awaiting payment.', $order->getId()));
        }
        $order->setPaymentStatus(Order::PAYMENT_PAID);
        $this->em->flush();
    }

    private function requireStatus(Order $order, string $status, string $action): void
    {
        if ($order->getOrderStatus() !== $status) {
            throw new \DomainException(sprintf('Order #%d is %s and cannot be %s.', $order->getId(), strtolower($order->getOrderStatus()), $action));
        }
    }
}
