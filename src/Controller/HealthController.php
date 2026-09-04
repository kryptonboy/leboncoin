<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController
{
    public function __construct(
        private readonly \Redis $redis,
    ) {
    }

    #[Route('/health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $redisStatus = $this->checkRedis();
        $isHealthy = 'ok' === $redisStatus;

        return new JsonResponse(
            [
                'status' => $isHealthy ? 'ok' : 'degraded',
                'redis' => $redisStatus,
            ],
            $isHealthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }

    private function checkRedis(): string
    {
        try {
            return $this->redis->ping() ? 'ok' : 'unreachable';
        } catch (\RedisException) {
            return 'unreachable';
        }
    }
}
