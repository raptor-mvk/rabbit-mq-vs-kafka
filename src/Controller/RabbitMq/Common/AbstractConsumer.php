<?php

namespace App\Controller\RabbitMq\Common;

use JsonException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use function App\Controller\RabbitMq\createFromQueue;

/**
 * @template T
 */
abstract class AbstractConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        /** @var MessageInterface<T> $messageClass */
        $messageClass = $this->getMessageClass();
        try {
            /** @var T $message */
            $message = $messageClass::createFromQueue($msg);
            $errors = $this->validator->validate($message);
            if ($errors->count() > 0) {
                return $this->reject((string)$errors);
            }
        } catch (JsonException $e) {
            return $this->reject($e->getMessage());
        }

        try {
            return $this->handle($message);
        } catch (Throwable $e) {
            return $this->reject($e->getMessage());
        } finally {
            $this->finalize();
        }
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getMessageClass(): string;

    abstract protected function handle(MessageInterface $message): int;

    protected function finalize(): void
    {
    }

    protected function reject(string $error): int
    {
        echo "Incorrect message: $error";

        return self::MSG_REJECT;
    }
}
