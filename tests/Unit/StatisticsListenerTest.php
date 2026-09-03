<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Dto\FizzBuzzRequest;
use App\EventListener\StatisticsListener;
use App\Tests\Fake\InMemoryStatisticsTracker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class StatisticsListenerTest extends TestCase
{
    public function testRecordsHitWhenFizzBuzzRequestAttributeIsPresent(): void
    {
        $tracker = new InMemoryStatisticsTracker();
        $listener = new StatisticsListener($tracker);

        $fizzBuzzRequest = new FizzBuzzRequest(3, 5, 15, 'fizz', 'buzz');
        $request = new Request();
        $request->attributes->set('fizzbuzz_request', $fizzBuzzRequest);

        $listener->__invoke($this->buildTerminateEvent($request));

        $mostFrequent = $tracker->getMostFrequent();
        $this->assertNotNull($mostFrequent);
        $this->assertSame(1, $mostFrequent->hits);
    }

    public function testDoesNothingWhenFizzBuzzRequestAttributeIsMissing(): void
    {
        $tracker = new InMemoryStatisticsTracker();
        $listener = new StatisticsListener($tracker);

        $request = new Request();

        $listener->__invoke($this->buildTerminateEvent($request));
        $this->assertNull($tracker->getMostFrequent());
    }

    private function buildTerminateEvent(Request $request): TerminateEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new TerminateEvent($kernel, $request, new Response());
    }
}
