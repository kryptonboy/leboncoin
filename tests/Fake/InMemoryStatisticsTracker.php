<?php

declare(strict_types=1);

namespace App\Tests\Fake;

use App\Dto\FizzBuzzRequest;
use App\Dto\FizzBuzzStatistic;
use App\Service\StatisticsTracker\StatisticsTrackerInterface;

/**
 * Test only class to avoid querying Redis while unit testing App\EventListener\StatisticsListener and App\Controller\StatisticsController.
 */
final class InMemoryStatisticsTracker implements StatisticsTrackerInterface
{
    /** @var array<string, int> */
    private array $hits = [];

    /** @var array<string, FizzBuzzRequest> */
    private array $requests = [];

    public function recordHit(FizzBuzzRequest $fizzBuzzRequest): void
    {
        $key = $fizzBuzzRequest->getKey();

        $this->hits[$key] = ($this->hits[$key] ?? 0) + 1;
        $this->requests[$key] ??= $fizzBuzzRequest;
    }

    public function getMostFrequent(): ?FizzBuzzStatistic
    {
        if (empty($this->hits)) {
            return null;
        }

        arsort($this->hits);
        $topKey = array_key_first($this->hits);

        return new FizzBuzzStatistic(
            fizzBuzzRequest: $this->requests[$topKey],
            hits: $this->hits[$topKey],
        );
    }

    public function reset(): void
    {
        $this->hits = [];
        $this->requests = [];
    }
}
