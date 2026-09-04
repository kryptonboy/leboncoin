<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HealthControllerTest extends WebTestCase
{
    public function testHealthReturnsOkWhenRedisIsReachable(): void
    {
        $client = self::createClient();

        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('ok', $data['status']);
        self::assertSame('ok', $data['redis']);
    }
}
