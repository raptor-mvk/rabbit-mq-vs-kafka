<?php

namespace App\Domain\Bus;

use App\Domain\Types\MetricsEnum;

interface MetricsBusInterface
{
    public function increment(MetricsEnum $metric, ?float $sampleRate = null): void;
}
