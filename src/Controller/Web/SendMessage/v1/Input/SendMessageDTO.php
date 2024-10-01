<?php

namespace App\Controller\Web\SendMessage\v1\Input;

use App\Domain\Types\QueueEnum;
use Symfony\Component\Validator\Constraints as Assert;

class SendMessageDTO
{
    public function __construct(
        #[Assert\Positive]
        public readonly int $eventsCountPerUser,
        #[Assert\Positive]
        public readonly int $usersCount,
        public readonly bool $useRoutingKeys,
        public readonly bool $useConsistentHash,
        #[Assert\Choice(callback: [QueueEnum::class, 'values'])]
        public readonly string $queue,
    ) {
    }
}
