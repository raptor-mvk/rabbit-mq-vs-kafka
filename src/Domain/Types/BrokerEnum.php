<?php

namespace App\Domain\Types;

enum BrokerEnum: string
{
    case Kafka = 'kafka';
    case RabbitMq = 'rabbitmq';

    public static function values(): array
    {
        return array_map(static fn(self $value): string => $value->value, self::cases());
    }
}
