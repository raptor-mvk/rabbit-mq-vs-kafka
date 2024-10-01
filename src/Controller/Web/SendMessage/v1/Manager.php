<?php

namespace App\Controller\Web\SendMessage\v1;

use App\Controller\Web\SendMessage\v1\Input\SendMessageDTO;
use App\Domain\Bus\KafkaBusInterface;
use App\Domain\Bus\RabbitMqBusInterface;
use App\Domain\Service\EventLogService;
use App\Domain\Types\QueueEnum;
use App\Domain\Types\TypeEnum;
use JsonException;
use Random\RandomException;

class Manager
{
    private const PARTITIONS_COUNT = 4;
    private const ROUTING_KEY_PREFIX = 'group';

    public function __construct(
        private readonly EventLogService $eventLogService,
        private readonly KafkaBusInterface $kafkaBus,
        private readonly RabbitMqBusInterface $rabbitMqBus,

    ) {
    }

    /**
     * @throws RandomException
     * @throws JsonException
     */
    public function sendMessages(SendMessageDTO $sendMessageDTO): float {
        $userId = $this->eventLogService->getNextUserId();
        $startTime = microtime(true);
        $firstReceivedAt = $this->eventLogService->getNextReceivedAt();
        for ($i = 0; $i < $sendMessageDTO->usersCount; $i++) {
            $receivedAt = clone $firstReceivedAt;
            $message = ['userId' => $userId + $i];
            $partition = $i % self::PARTITIONS_COUNT;
            $type = TypeEnum::CREATE;
            for ($j = 0; $j < $sendMessageDTO->eventsCountPerUser; $j++, $type = $type->getNext()) {
                $message['type'] = $type;
                $message['receivedAt'] = $receivedAt->modify('+1 sec')->format('Y-m-d H:i:s');
                $message['payload'] = bin2hex(random_bytes(10000));
                $queue = QueueEnum::from($sendMessageDTO->queue);
                if ($sendMessageDTO->useRoutingKeys) {
                    $this->kafkaBus->sendMessage($queue, $message, $partition);
                    $this->rabbitMqBus->sendMessage($queue, $message, self::ROUTING_KEY_PREFIX.$partition);
                } else if ($sendMessageDTO->useConsistentHash) {
                    $this->kafkaBus->sendMessage($queue, $message, null, (string)($userId + $i));
                    $this->rabbitMqBus->sendMessage($queue, $message, (string)($userId + $i));
                } else {
                    $this->kafkaBus->sendMessage($queue, $message);
                    $this->rabbitMqBus->sendMessage($queue, $message);
                }
            }
        }

        return microtime(true) - $startTime;
    }
}
