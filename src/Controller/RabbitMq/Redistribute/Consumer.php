<?php

namespace App\Controller\RabbitMq\Redistribute;

use App\Controller\RabbitMq\Common\AbstractConsumer;
use App\Controller\RabbitMq\Common\MessageInterface;
use App\Controller\RabbitMq\Redistribute\Input\Message;
use App\Domain\Bus\RabbitMqBusInterface;
use App\Domain\Types\QueueEnum;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements AbstractConsumer<Message>
 */
class Consumer extends AbstractConsumer
{
    public function __construct(
        ValidatorInterface $validator,
        private readonly RabbitMqBusInterface $rabbitMqBus,
    ) {
        parent::__construct($validator);
    }

    protected function getMessageClass(): string
    {
        return Message::class;
    }

    /**
     * @param Message $message
     */
    protected function handle(MessageInterface $message): int
    {
        $this->rabbitMqBus->sendMessage(QueueEnum::Hashed, $message->toQueue(), (string)$message->userId);

        return self::MSG_ACK;
    }
}
