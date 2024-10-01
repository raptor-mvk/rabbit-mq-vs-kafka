<?php

namespace App\Domain\Types;

enum MetricsEnum: string
{
    case RabbitMqProcessed = 'rabbit_mq.processed.total';
    case RabbitMqConsumer0Processed = 'rabbit_mq.processed.consumer_0';
    case RabbitMqConsumer1Processed = 'rabbit_mq.processed.consumer_1';
    case RabbitMqConsumer2Processed = 'rabbit_mq.processed.consumer_2';
    case RabbitMqConsumer3Processed = 'rabbit_mq.processed.consumer_3';
    case RabbitMqConsumer4Processed = 'rabbit_mq.processed.consumer_4';
    case RabbitMqConsumer5Processed = 'rabbit_mq.processed.consumer_5';
    case RabbitMqConsumer6Processed = 'rabbit_mq.processed.consumer_6';
    case RabbitMqConsumer7Processed = 'rabbit_mq.processed.consumer_7';
    case RabbitMqConsumer8Processed = 'rabbit_mq.processed.consumer_8';
    case RabbitMqConsumer9Processed = 'rabbit_mq.processed.consumer_9';
    case RabbitMqBrokenOrder = 'rabbit_mq.broken_order';
    case RabbitMqRaceCondition = 'rabbit_mq.race_condition';
    case KafkaProcessed = 'kafka.processed.total';
    case KafkaConsumer0Processed = 'kafka.processed.consumer_0';
    case KafkaConsumer1Processed = 'kafka.processed.consumer_1';
    case KafkaConsumer2Processed = 'kafka.processed.consumer_2';
    case KafkaConsumer3Processed = 'kafka.processed.consumer_3';
    case KafkaConsumer4Processed = 'kafka.processed.consumer_4';
    case KafkaConsumer5Processed = 'kafka.processed.consumer_5';
    case KafkaConsumer6Processed = 'kafka.processed.consumer_6';
    case KafkaConsumer7Processed = 'kafka.processed.consumer_7';
    case KafkaConsumer8Processed = 'kafka.processed.consumer_8';
    case KafkaConsumer9Processed = 'kafka.processed.consumer_9';
    case KafkaBrokenOrder = 'kafka.broken_order';
    case KafkaRaceCondition = 'kafka.race_condition';

    public static function values(): array
    {
        return array_map(static fn(self $value): string => $value->value, self::cases());
    }
}
