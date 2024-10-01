<?php

namespace App\Domain\Bus;

use App\Domain\Types\QueueEnum;

interface KafkaBusInterface
{
    public function sendMessage(QueueEnum $queue, array $message, ?int $partition = null, ?string $messageKey = null): void;
}
