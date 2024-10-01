<?php

namespace App\Controller\Kafka\Common;

use JsonException;
use RdKafka\Message;

interface MessageInterface
{
    /**
     * @throws JsonException
     */
    public static function createFromQueue(Message $message): self;
}
