<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Controller\HealthController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class HealthControllerTest extends TestCase
{
    public function testReturnsOkWhenRedisRespondsToPing(): void
    {
        $redis = $this->createStub(\Redis::class);
        $redis->method('ping')->willReturn(true);

        $controller = new HealthController($redis);

        $response = $controller->__invoke();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('ok', $data['status']);
        self::assertSame('ok', $data['redis']);
    }

    public function testReturnsDegradedWhenPingReturnsFalse(): void
    {
        $redis = $this->createStub(\Redis::class);
        $redis->method('ping')->willReturn(false);

        $controller = new HealthController($redis);

        $response = $controller->__invoke();

        self::assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('degraded', $data['status']);
        self::assertSame('unreachable', $data['redis']);
    }

    public function testReturnsDegradedWhenRedisThrowsException(): void
    {
        $redis = $this->createStub(\Redis::class);
        $redis->method('ping')->willThrowException(new \RedisException());

        $controller = new HealthController($redis);

        $response = $controller->__invoke();

        self::assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('degraded', $data['status']);
        self::assertSame('unreachable', $data['redis']);
    }
}
