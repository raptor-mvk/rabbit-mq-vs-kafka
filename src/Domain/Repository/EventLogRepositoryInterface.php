<?php

namespace App\Domain\Repository;

use App\Domain\Entity\EventLog;
use App\Domain\Exception\PossibleRaceConditionException;
use App\Domain\Types\BrokerEnum;
use DateTime;

interface EventLogRepositoryInterface
{
    public function getMaxUserId(): ?int;

    public function getMaxReceivedAt(): ?string;

    public function findByUserIdAndBroker(int $userId, BrokerEnum $broker): ?EventLog;

    /**
     * @throws PossibleRaceConditionException
     */
    public function store(EventLog $eventLog): void;

    public function resetDatabaseConnection(): void;
}
