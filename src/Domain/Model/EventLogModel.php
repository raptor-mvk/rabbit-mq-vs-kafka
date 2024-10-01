<?php

namespace App\Domain\Model;

use App\Domain\Types\BrokerEnum;
use App\Domain\Types\TypeEnum;
use DateTime;

class EventLogModel
{
    public function __construct(
        public readonly int $userId,
        public readonly TypeEnum $type,
        public readonly BrokerEnum $broker,
        public readonly string $payload,
        public readonly DateTime $receivedAt
    ) {
    }
}
