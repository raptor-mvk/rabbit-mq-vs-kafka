<?php

namespace App\Infrastructure\Bus;

use App\Domain\Bus\RabbitMqBusInterface;
use App\Domain\Types\QueueEnum;
use JsonException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class RabbitMqBus implements RabbitMqBusInterface
{
    /** @var array<string, ProducerInterface> */
    private array $producers = [];

    public function registerProducer(QueueEnum $name, ProducerInterface $producer): void
    {
        $this->producers[$name->value] = $producer;
    }

    /**
     * @throws JsonException
     */
    public function sendMessage(QueueEnum $queue, array $message, ?string $routingKey = null): void
    {
        /** @var ProducerInterface|null $producer */
        $producer = $this->producers[$queue->value] ?? null;
        $producer?->publish(json_encode($message, JSON_THROW_ON_ERROR), $routingKey);
    }
}
