<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Controller\StatisticsResetController;
use App\Dto\FizzBuzzRequest;
use App\Tests\Fake\InMemoryStatisticsTracker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class StatisticsResetControllerTest extends TestCase
{
    public function testRejectsRequestWithoutToken(): void
    {
        $tracker = new InMemoryStatisticsTracker();
        $controller = new StatisticsResetController($tracker, 'correct-secret');

        $tracker->recordHit(new FizzBuzzRequest(int1: 3, int2: 5, limit: 15, str1: 'fizz', str2: 'buzz'));

        $response = $controller->__invoke(new Request());

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertNotNull($tracker->getMostFrequent());
    }

    public function testRejectsRequestWithWrongToken(): void
    {
        $tracker = new InMemoryStatisticsTracker();
        $controller = new StatisticsResetController($tracker, 'correct-secret');

        $tracker->recordHit(new FizzBuzzRequest(int1: 3, int2: 5, limit: 15, str1: 'fizz', str2: 'buzz'));

        $request = new Request();
        $request->headers->set('X-Reset-Token', 'wrong-secret');

        $response = $controller->__invoke($request);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertNotNull($tracker->getMostFrequent());
    }

    public function testRejectsAnyRequestWhenNoTokenIsConfigured(): void
    {
        $tracker = new InMemoryStatisticsTracker();
        $controller = new StatisticsResetController($tracker, '');

        $tracker->recordHit(new FizzBuzzRequest(int1: 3, int2: 5, limit: 15, str1: 'fizz', str2: 'buzz'));

        $request = new Request();
        $request->headers->set('X-Reset-Token', ''); // even an empty match should not be accepted

        $response = $controller->__invoke($request);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertNotNull($tracker->getMostFrequent());
    }

    public function testResetsStatisticsWithCorrectToken(): void
    {
        $tracker = new InMemoryStatisticsTracker();
        $controller = new StatisticsResetController($tracker, 'correct-secret');

        $tracker->recordHit(new FizzBuzzRequest(int1: 3, int2: 5, limit: 15, str1: 'fizz', str2: 'buzz'));

        $request = new Request();
        $request->headers->set('X-Reset-Token', 'correct-secret');

        $response = $controller->__invoke($request);

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertNull($tracker->getMostFrequent());
    }
}
