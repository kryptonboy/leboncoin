<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class StatisticsResetControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private \Redis $redis;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->redis = self::getContainer()->get(\Redis::class);
        $this->redis->flushAll();
    }

    protected function tearDown(): void
    {
        $this->redis->flushAll();
        parent::tearDown();
    }

    public function testResetRejectsRequestWithoutToken(): void
    {
        $this->client->request('DELETE', '/statistics');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testResetRejectsRequestWithWrongToken(): void
    {
        $this->client->request('DELETE', '/statistics', server: [
            'HTTP_X_RESET_TOKEN' => 'wrong-token',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testResetClearsStatisticsWithCorrectToken(): void
    {
        // Record a hit first
        $this->client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz');

        $this->client->request('GET', '/statistics');
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $data['hits']);

        // Reset
        $this->client->request('DELETE', '/statistics', server: [
            'HTTP_X_RESET_TOKEN' => $_ENV['RESET_TOKEN'],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Confirm it's actually cleared
        $this->client->request('GET', '/statistics');
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertNull($data['parameters']);
        self::assertSame(0, $data['hits']);
    }
}
