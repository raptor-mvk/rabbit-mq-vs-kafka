<?php

namespace App\Domain\Bus;

use App\Domain\Types\QueueEnum;

interface RabbitMqBusInterface
{
    public function sendMessage(QueueEnum $queue, array $message, ?string $routingKey = null): void;
}
