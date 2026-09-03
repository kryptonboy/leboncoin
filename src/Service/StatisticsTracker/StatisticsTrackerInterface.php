<?php

declare(strict_types=1);

namespace App\Service\StatisticsTracker;

use App\Dto\FizzBuzzRequest;
use App\Dto\FizzBuzzStatistic;

interface StatisticsTrackerInterface
{
    public function recordHit(FizzBuzzRequest $fizzBuzzRequest): void;

    public function getMostFrequent(): ?FizzBuzzStatistic;
}
