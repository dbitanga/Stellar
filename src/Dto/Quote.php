<?php

namespace App\Dto;

/** Money breakdown shown in the cart and charged at checkout. Amounts are decimal strings, like the database columns. */
final readonly class Quote
{
    public function __construct(
        public string $subtotal,
        public string $tax,
        public string $shippingProtection,
        public string $shipping,
        public string $total,
    ) {
    }
}
