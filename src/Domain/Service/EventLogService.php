<?php

namespace App\Domain\Service;

use App\Domain\Bus\MetricsBusInterface;
use App\Domain\Entity\EventLog;
use App\Domain\Exception\PossibleRaceConditionException;
use App\Domain\Model\EventLogModel;
use App\Domain\Repository\EventLogRepositoryInterface;
use App\Domain\Types\BrokerEnum;
use App\Domain\Types\MetricsEnum;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class EventLogService
{
    private const FIRST_ID = 0;

    public function __construct(
        private readonly EventLogRepositoryInterface $eventLogRepository,
        private readonly MetricsBusInterface $metricsBus,
    ) {
    }

    public function getNextUserId(): int
    {
        return ($this->eventLogRepository->getMaxUserId() ?? self::FIRST_ID) + 1;
    }

    public function getNextReceivedAt(): DateTime
    {
        $maxReceivedAt = $this->eventLogRepository->getMaxReceivedAt();

        if ($maxReceivedAt === null) {
            return new DateTime();
        }

        return (DateTime::createFromFormat('Y-m-d H:i:s', $maxReceivedAt))->modify('+1 sec');
    }

    public function ensureEventLog(EventLogModel $model): bool {
        while (true) {
            $eventLog = $this->eventLogRepository->findByUserIdAndBroker($model->userId, $model->broker) ??
                (new EventLog())->setUserId($model->userId)->setBroker($model->broker);
            $result = $eventLog->getReceivedAt() === null || $eventLog->getReceivedAt() < $model->receivedAt;
            $eventLog->setType($model->type);
            $eventLog->setPayload($model->payload);
            $eventLog->setReceivedAt($model->receivedAt);
            try {
                $this->eventLogRepository->store($eventLog);
            } catch (PossibleRaceConditionException) {
                match($model->broker) {
                    BrokerEnum::RabbitMq => $this->metricsBus->increment(MetricsEnum::RabbitMqRaceCondition),
                    BrokerEnum::Kafka => $this->metricsBus->increment(MetricsEnum::KafkaRaceCondition),
                };
                continue;
            }
            return $result;
        }
    }

    public function resetDatabaseConnection(): void
    {
        $this->eventLogRepository->resetDatabaseConnection();
    }
}
