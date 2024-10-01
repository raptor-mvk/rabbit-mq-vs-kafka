<?php

namespace App\Controller\Kafka\Redistribute;

use App\Controller\Kafka\Common\MessageInterface;
use App\Controller\Kafka\Redistribute\Input\Message;
use App\Domain\Bus\KafkaBusInterface;
use App\Domain\Types\QueueEnum;
use App\Controller\Kafka\Common\AbstractConsumer;
use SimPod\KafkaBundle\Kafka\Configuration;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements AbstractConsumer<Message>
 */
class Consumer extends AbstractConsumer
{
    /**
     * @param QueueEnum[] $topics
     */
    public function __construct(
        Configuration $configuration,
        array $topics,
        string $groupId,
        ValidatorInterface $validator,
        private readonly string $name,
        private readonly KafkaBusInterface $kafkaBus,
        private readonly int $coeff,
    ) {
        parent::__construct($configuration, $topics, $groupId, $validator);
    }

    protected function getMessageClass(): string
    {
        return Message::class;
    }

    /**
     * @param Message $message
     */
    protected function handle(MessageInterface $message): void
    {
        $this->kafkaBus->sendMessage(QueueEnum::Hashed, $message->toQueue(), $message->userId % $this->coeff);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
