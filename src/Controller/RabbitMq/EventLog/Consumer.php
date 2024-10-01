<?php

namespace App\Controller\RabbitMq\EventLog;

use App\Controller\RabbitMq\Common\AbstractConsumer;
use App\Controller\RabbitMq\Common\MessageInterface;
use App\Controller\RabbitMq\EventLog\Input\Message;
use App\Domain\Bus\MetricsBusInterface;
use App\Domain\Model\EventLogModel;
use App\Domain\Service\EventLogService;
use App\Domain\Types\BrokerEnum;
use App\Domain\Types\MetricsEnum;
use App\Domain\Types\TypeEnum;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements AbstractConsumer<Message>
 */
class Consumer extends AbstractConsumer
{
    public function __construct(
        ValidatorInterface $validator,
        private readonly EventLogService $eventLogService,
        private readonly MetricsEnum $metric,
        private readonly MetricsBusInterface $metricsBus,
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
        $model = new EventLogModel(
            $message->userId,
            TypeEnum::from($message->type),
            BrokerEnum::RabbitMq,
            $message->payload,
            $message->receivedAt
        );
        $result = $this->eventLogService->ensureEventLog($model);
        if (!$result) {
            $this->metricsBus->increment(MetricsEnum::RabbitMqBrokenOrder);
        }
        $this->metricsBus->increment(MetricsEnum::RabbitMqProcessed);
        $this->metricsBus->increment($this->metric);

        return self::MSG_ACK;
    }

    protected function finalize(): void
    {
        $this->eventLogService->resetDatabaseConnection();
    }
}
