<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\EventLog;
use App\Domain\Exception\PossibleRaceConditionException;
use App\Domain\Repository\EventLogRepositoryInterface;
use App\Domain\Types\BrokerEnum;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

class EventLogRepository extends ServiceEntityRepository implements EventLogRepositoryInterface
{
    public function __construct(private readonly ManagerRegistry $registry)
    {
        parent::__construct($registry, EventLog::class);
    }

    public function getMaxUserId(): ?int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select($qb->expr()->max('e.userId'))
            ->from(EventLog::class, 'e')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getMaxReceivedAt(): ?string
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select($qb->expr()->max('e.receivedAt'))
            ->from(EventLog::class, 'e')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByUserIdAndBroker(int $userId, BrokerEnum $broker): ?EventLog
    {
        /** @var EventLog|null $result */
        $result = $this->findOneBy(['userId' => $userId, 'broker' => $broker]);

        return $result;
    }

    /**
     * @throws PossibleRaceConditionException
     */
    public function store(EventLog $eventLog): void
    {
        try {
            $this->getEntityManager()->persist($eventLog);
            $this->getEntityManager()->flush();
        } catch (UniqueConstraintViolationException) {
            $this->registry->resetManager();
            throw new PossibleRaceConditionException();
        }
    }

    public function resetDatabaseConnection(): void
    {
        $this->getEntityManager()->clear();
        $this->getEntityManager()->getConnection()->close();
    }
}
