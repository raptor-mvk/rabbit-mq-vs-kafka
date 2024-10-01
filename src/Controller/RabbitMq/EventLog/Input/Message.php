<?php

namespace App\Controller\RabbitMq\EventLog\Input;

use App\Controller\RabbitMq\Common\MessageInterface;
use App\Domain\Types\TypeEnum;
use DateTime;
use JsonException;
use PhpAmqpLib\Message\AMQPMessage;
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
    public static function createFromQueue(AMQPMessage $message): self
    {
        $data = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return new self(
            $data['userId'],
            $data['type'],
            $data['payload'],
            DateTime::createFromFormat('Y-m-d H:i:s', $data['receivedAt']),
        );
    }
}
