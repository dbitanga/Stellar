<?php

namespace App\Service;

use App\Dto\Quote;
use App\Entity\Order;
use App\Entity\OrderItem;

/**
 * Store fees: 10% tax, 2% shipping protection, and shipping of ₱250 plus ₱100 per item.
 */
class Pricing
{
    private const TAX_RATE = '0.10';
    private const PROTECTION_RATE = '0.02';
    private const SHIPPING_BASE = '250.00';
    private const SHIPPING_PER_ITEM = '100.00';

    /** @param iterable<array{price: string, quantity: int}> $lines */
    public function quote(iterable $lines): Quote
    {
        $subtotal = '0.00';
        $items = 0;
        foreach ($lines as ['price' => $price, 'quantity' => $quantity]) {
            $subtotal = bcadd($subtotal, bcmul($price, (string) $quantity, 2), 2);
            $items += $quantity;
        }

        $tax = bcmul($subtotal, self::TAX_RATE, 2);
        $protection = bcmul($subtotal, self::PROTECTION_RATE, 2);
        $shipping = $items > 0 ? bcadd(self::SHIPPING_BASE, bcmul(self::SHIPPING_PER_ITEM, (string) $items, 2), 2) : '0.00';

        return new Quote($subtotal, $tax, $protection, $shipping, bcadd(bcadd($subtotal, $tax, 2), bcadd($protection, $shipping, 2), 2));
    }

    /** The breakdown of a placed order, from the prices it was bought at. */
    public function quoteOrder(Order $order): Quote
    {
        return $this->quote(array_map(
            static fn (OrderItem $item): array => ['price' => (string) $item->getPriceAtPurchase(), 'quantity' => (int) $item->getQuantity()],
            $order->getItems()->toArray(),
        ));
    }
}
