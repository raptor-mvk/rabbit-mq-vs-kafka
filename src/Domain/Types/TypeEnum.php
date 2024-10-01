<?php

namespace App\Domain\Types;

enum TypeEnum: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';

    public function getNext(): self
    {
        return match($this) {
            self::CREATE => self::UPDATE,
            self::UPDATE => self::DELETE,
            self::DELETE => self::CREATE,
        };
    }

    public static function values(): array
    {
        return array_map(static fn(self $value): string => $value->value, self::cases());
    }
}
