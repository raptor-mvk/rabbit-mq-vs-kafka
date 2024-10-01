<?php

namespace App\Infrastructure\Bus;

use App\Domain\Bus\KafkaBusInterface;
use App\Domain\Types\QueueEnum;
use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\ProducerTopic;
use RuntimeException;
use SimPod\KafkaBundle\Kafka\Configuration;

class KafkaBus implements KafkaBusInterface
{
    public const DEFAULT_TIMEOUT = 1000;
    public const FLUSH_RETRY_COUNT = 10;

    /**
     * @var ProducerTopic[]
     */
    private array $topics = [];

    private Producer $producer;

    public function __construct(private readonly Configuration $configuration) {
        $this->producer = new Producer(new Conf());
        $this->producer->addBrokers($this->configuration->getBootstrapServers());
    }

    public function sendMessage(QueueEnum $queue, array $message, ?int $partition = null, ?string $messageKey = null): void
    {
        $partition = $partition ?? RD_KAFKA_PARTITION_UA;
        $payload = json_encode($message, JSON_THROW_ON_ERROR);

        $this->getTopic($queue->value)->produce($partition, 0, $payload, $messageKey);

        for ($i = 0; $i < self::FLUSH_RETRY_COUNT; ++$i) {
            if ($this->producer->flush(self::DEFAULT_TIMEOUT) === RD_KAFKA_RESP_ERR_NO_ERROR) {
                return;
            }
        }

        throw new RuntimeException($payload);
    }

    private function getTopic(string $name): ProducerTopic
    {
        if (false === isset($this->topics[$name])) {
            $this->topics[$name] = $this->producer->newTopic($name);
        }

        return $this->topics[$name];
    }
}
