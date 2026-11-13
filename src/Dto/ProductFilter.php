<?php

namespace App\Dto;

use App\Entity\Product;
use App\Enum\ProductCategory;
use Symfony\Component\Validator\Constraints as Assert;

/** Search and filter options for the product list, bound from the query string. */
final readonly class ProductFilter
{
    public const STATUSES = ['visible', 'hidden', 'low_stock'];

    #[Assert\Length(max: 100)]
    public ?string $q;

    #[Assert\Choice(callback: [ProductCategory::class, 'values'])]
    public ?string $category;

    #[Assert\Choice(choices: Product::TIERS)]
    public ?string $tier;

    #[Assert\Choice(choices: self::STATUSES)]
    public ?string $status;

    public function __construct(
        ?string $q = null,
        ?string $category = null,
        ?string $tier = null,
        ?string $status = null,
        public bool $mine = false,
    ) {
        // The filter form submits untouched fields as "", which means "no filter".
        $this->q = '' === trim((string) $q) ? null : trim($q);
        $this->category = '' === $category ? null : $category;
        $this->tier = '' === $tier ? null : $tier;
        $this->status = '' === $status ? null : $status;
    }

    public function isActive(): bool
    {
        return null !== $this->q || null !== $this->category || null !== $this->tier || null !== $this->status || $this->mine;
    }
}
