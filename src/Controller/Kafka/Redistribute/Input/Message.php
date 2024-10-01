<?php

namespace App\Controller\Kafka\Redistribute\Input;

use App\Controller\Kafka\Common\MessageInterface;
use App\Domain\Types\TypeEnum;
use DateTime;
use JsonException;
use RdKafka\Message as KafkaMessage;
use Symfony\Component\Validator\Constraints as Assert;

class Message implements MessageInterface
{
    public function __construct(
        #[Assert\Positive]
        public readonly int $userId,
        #[Assert\Choice(callback: [TypeEnum::class, 'values'])]
        public readonly string $type,
        public readonly string $payload,
        public readonly DateTime $receivedAt,
    ) {
    }

    /**
     * @throws JsonException
     */
    public static function createFromQueue(KafkaMessage $message): self
    {
        $data = json_decode($message->payload, true, 512, JSON_THROW_ON_ERROR);

        return new self(
            $data['userId'],
            $data['type'],
            $data['payload'],
            DateTime::createFromFormat('Y-m-d H:i:s', $data['receivedAt']),
        );
    }

    public function toQueue(): array
    {
        return [
            'userId' => $this->userId,
            'type' => $this->type,
            'payload' => $this->payload,
            'receivedAt' => $this->receivedAt->format('Y-m-d H:i:s'),
        ];
    }
}
