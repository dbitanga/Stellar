<?php

namespace App\Enum;

/** The fixed product lines a laptop can belong to; stored as a string in products.category. */
enum ProductCategory: string
{
    case Gaming = 'gaming';
    case Personal = 'personal';
    case Workstation = 'workstation';

    public function label(): string
    {
        return match ($this) {
            self::Gaming => 'Gaming',
            self::Personal => 'Personal',
            self::Workstation => 'Workstation',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
