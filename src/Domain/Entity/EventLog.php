<?php

namespace App\Domain\Entity;

use App\Domain\Types\BrokerEnum;
use App\Domain\Types\TypeEnum;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'event_log__user_id__broker__uniq', columns: ['user_id', 'broker'])]
class EventLog
{
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $userId;

    #[ORM\Column(type: 'string', nullable: false, enumType: TypeEnum::class)]
    private TypeEnum $type;

    #[ORM\Column(type: 'string', nullable: false, enumType: BrokerEnum::class)]
    private BrokerEnum $broker;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $payload;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?DateTime $receivedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): TypeEnum
    {
        return $this->type;
    }

    public function setType(TypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getReceivedAt(): ?DateTime
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(DateTime $receivedAt): self
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getBroker(): BrokerEnum
    {
        return $this->broker;
    }

    public function setBroker(BrokerEnum $broker): self
    {
        $this->broker = $broker;

        return $this;
    }
}
