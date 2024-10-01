<?php

namespace App\Controller\Kafka\EventLog;

use App\Controller\Kafka\Common\MessageInterface;
use App\Controller\Kafka\EventLog\Input\Message;
use App\Domain\Types\BrokerEnum;
use App\Domain\Types\QueueEnum;
use App\Controller\Kafka\Common\AbstractConsumer;
use App\Domain\Bus\MetricsBusInterface;
use App\Domain\Model\EventLogModel;
use App\Domain\Service\EventLogService;
use App\Domain\Types\MetricsEnum;
use App\Domain\Types\TypeEnum;
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
        private readonly EventLogService $eventLogService,
        private readonly MetricsEnum $metric,
        private readonly MetricsBusInterface $metricsBus,
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
        $model = new EventLogModel(
            $message->userId,
            TypeEnum::from($message->type),
            BrokerEnum::Kafka,
            $message->payload,
            $message->receivedAt
        );
        $result = $this->eventLogService->ensureEventLog($model);
        if (!$result) {
            $this->metricsBus->increment(MetricsEnum::KafkaBrokenOrder);
        }
        $this->metricsBus->increment(MetricsEnum::KafkaProcessed);
        $this->metricsBus->increment($this->metric);
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function finalize(): void
    {
        $this->eventLogService->resetDatabaseConnection();
    }
}
