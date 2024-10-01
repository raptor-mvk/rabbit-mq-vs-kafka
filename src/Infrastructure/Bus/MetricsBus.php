<?php

namespace App\Infrastructure\Bus;

use App\Domain\Bus\MetricsBusInterface;
use App\Domain\Types\MetricsEnum;
use Domnikl\Statsd\Client;
use Domnikl\Statsd\Connection\UdpSocket;

class MetricsBus implements MetricsBusInterface
{
    private const DEFAULT_SAMPLE_RATE = 1.0;

    private Client $client;

    public function __construct(string $host, int $port, string $namespace)
    {
        $connection = new UdpSocket($host, $port);
        $this->client = new Client($connection, $namespace);
    }

    public function increment(MetricsEnum $metric, ?float $sampleRate = null): void
    {
        $this->client->increment($metric->value, $sampleRate ?? self::DEFAULT_SAMPLE_RATE, []);
    }
}
