<?php

namespace App\Controller\RabbitMq\Common;

use JsonException;
use PhpAmqpLib\Message\AMQPMessage;

interface MessageInterface
{
    /**
     * @throws JsonException
     */
    public static function createFromQueue(AMQPMessage $message): self;
}
