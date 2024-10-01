<?php

namespace App\Domain\Types;

enum QueueEnum: string
{
    case Scaling = 'scaling';
    case Distributed = 'distributed';
    case Hashed = 'hashed';
    case Redistribute = 'redistribute';

    public static function values(): array
    {
        return array_map(static fn(self $value): string => $value->value, self::cases());
    }
}
