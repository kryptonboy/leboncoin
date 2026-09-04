<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Dto\FizzBuzzRequest;
use App\Tests\Fake\InMemoryStatisticsTracker;
use PHPUnit\Framework\TestCase;

final class InMemoryStatisticsTrackerTest extends TestCase
{
    public function testResetClearsAllRecordedHits(): void
    {
        $tracker = new InMemoryStatisticsTracker();
        $tracker->recordHit(new FizzBuzzRequest(int1: 3, int2: 5, limit: 15, str1: 'fizz', str2: 'buzz'));
        $tracker->recordHit(new FizzBuzzRequest(int1: 3, int2: 5, limit: 15, str1: 'fizz', str2: 'buzz'));

        self::assertNotNull($tracker->getMostFrequent());

        $tracker->reset();

        self::assertNull($tracker->getMostFrequent());
    }

    public function testResetOnEmptyTrackerDoesNotThrow(): void
    {
        $tracker = new InMemoryStatisticsTracker();

        $tracker->reset();

        self::assertNull($tracker->getMostFrequent());
    }
}
