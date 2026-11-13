<?php

namespace App\Service;

use App\Dto\Checkout;
use App\Dto\Quote;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The customer's shopping cart, saved in the cart table so it survives logging out.
 * Throws \DomainException with a user-facing message when an action isn't allowed.
 */
class CartService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CartRepository $carts,
        private readonly Pricing $pricing,
        private readonly OrderService $orders,
    ) {
    }

    /** @return Cart[] */
    public function lines(User $user): array
    {
        return $this->carts->findForUser($user);
    }

    public function quote(User $user): Quote
    {
        return $this->pricing->quote(array_map(
            static fn (Cart $line): array => ['price' => (string) $line->getProduct()->getPrice(), 'quantity' => $line->getQuantity()],
            $this->lines($user),
        ));
    }

    public function count(User $user): int
    {
        return array_sum(array_map(static fn (Cart $line): int => $line->getQuantity(), $this->lines($user)));
    }

    public function add(User $user, Product $product): void
    {
        if (!$product->isVisible()) {
            throw new \DomainException(sprintf('%s is not available.', $product->getName()));
        }

        $line = $this->carts->findOneBy(['user' => $user, 'product' => $product])
            ?? (new Cart())->setUser($user)->setProduct($product)->setQuantity(0);
        $this->setQuantity($line, $line->getQuantity() + 1);
        $this->em->persist($line);
        $this->em->flush();
    }

    /** Changes a line by +1 or -1; going below one removes it. */
    public function change(User $user, Product $product, int $delta): void
    {
        $line = $this->carts->findOneBy(['user' => $user, 'product' => $product]);
        if (!$line) {
            return;
        }

        $quantity = $line->getQuantity() + $delta;
        if ($quantity < 1) {
            $this->em->remove($line);
        } else {
            $this->setQuantity($line, $quantity);
        }
        $this->em->flush();
    }

    public function remove(User $user, Product $product): void
    {
        $line = $this->carts->findOneBy(['user' => $user, 'product' => $product]);
        if ($line) {
            $this->em->remove($line);
            $this->em->flush();
        }
    }

    /** Turns the cart into an order and empties it, all or nothing. */
    public function checkout(User $user, Checkout $checkout): Order
    {
        return $this->em->wrapInTransaction(function () use ($user, $checkout): Order {
            $lines = $this->lines($user);
            $quantities = [];
            foreach ($lines as $line) {
                $quantities[$line->getProduct()->getId()] = $line->getQuantity();
            }

            $order = $this->orders->place($user, $quantities, $checkout);

            foreach ($lines as $line) {
                $this->em->remove($line);
            }
            $this->em->flush();

            return $order;
        });
    }

    private function setQuantity(Cart $line, int $quantity): void
    {
        $stock = (int) $line->getProduct()->getStock();
        if ($quantity > $stock) {
            throw new \DomainException(sprintf('Only %d of %s left in stock.', $stock, $line->getProduct()->getName()));
        }
        $line->setQuantity($quantity);
    }
}
