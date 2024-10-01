<?php

namespace App\Controller\Kafka\Common;

use App\Controller\Kafka\Common\MessageInterface;
use App\Domain\Types\QueueEnum;
use RdKafka\Message;
use SimPod\Kafka\Clients\Consumer\ConsumerConfig;
use SimPod\Kafka\Clients\Consumer\KafkaConsumer;
use SimPod\KafkaBundle\Kafka\Configuration;
use SimPod\KafkaBundle\Kafka\Clients\Consumer\NamedConsumer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @template T
 */
abstract class AbstractConsumer implements NamedConsumer
{
    private const TIMEOUT_MS = 2000;

    /**
     * @param QueueEnum[] $topics
     */
    public function __construct(
        private readonly Configuration $configuration,
        private readonly array $topics,
        private readonly string $groupId,
        private readonly ValidatorInterface $validator,
    )
    {
    }

    public function run(): void
    {
        $kafkaConsumer = new KafkaConsumer($this->getConfig());

        $kafkaConsumer->subscribe(array_map(static fn (QueueEnum $topic): string => $topic->value, $this->topics));

        while (true) {
            $kafkaConsumer->start(
                self::TIMEOUT_MS,
                function (Message $msg) use ($kafkaConsumer) : void {
                    try {
                        /** @var MessageInterface<T> $messageClass */
                        $messageClass = $this->getMessageClass();
                        $message = $messageClass::createFromQueue($msg);
                        $errors = $this->validator->validate($message);
                        if ($errors->count() > 0) {
                            $this->reject((string)$errors);
                        } else {
                            $this->handle($message);
                        }
                        $kafkaConsumer->commit($msg);
                    } finally {
                        $this->finalize();
                    }
                }
            );
        }
    }

    abstract protected function handle(MessageInterface $message): void;

    /**
     * @return class-string<T>
     */
    abstract protected function getMessageClass(): string;

    protected function finalize(): void
    {
    }

    protected function reject(string $error): void
    {
        echo "Incorrect message: $error";
    }

    private function getConfig(): ConsumerConfig
    {
        $config = new ConsumerConfig();

        $config->set(ConsumerConfig::BOOTSTRAP_SERVERS_CONFIG, $this->configuration->getBootstrapServers());
        $config->set(ConsumerConfig::ENABLE_AUTO_COMMIT_CONFIG, false);
        $config->set(ConsumerConfig::CLIENT_ID_CONFIG, $this->configuration->getClientIdWithHostname());
        $config->set(ConsumerConfig::AUTO_OFFSET_RESET_CONFIG, 'earliest');
        $config->set(ConsumerConfig::GROUP_ID_CONFIG, $this->groupId);

        return $config;
    }
}
