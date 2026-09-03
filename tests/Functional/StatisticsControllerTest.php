<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StatisticsControllerTest extends WebTestCase
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

    public function testStatisticsReturnsNullWhenNoRequestRecordedYet(): void
    {
        $this->client->request('GET', '/statistics');

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertNull($data['parameters']);
        self::assertSame(0, $data['hits']);
    }

    public function testStatisticsReturnsMostFrequentRequestAfterMultipleHits(): void
    {
        // hit A twice
        $this->client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz');
        $this->client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz');

        // hit B once
        $this->client->request('GET', '/fizzbuzz?int1=2&int2=7&limit=15&str1=foo&str2=bar');

        $this->client->request('GET', '/statistics');

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(3, $data['parameters']['int1']);
        self::assertSame(5, $data['parameters']['int2']);
        self::assertSame(2, $data['hits']);
    }

    public function testTieIsBrokenByWhichCombinationReachedTheScoreFirst(): void
    {
        // hit A reaches 1 hit first
        $this->client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz');

        // hit B reaches 1 hit slightly after
        $this->client->request('GET', '/fizzbuzz?int1=2&int2=7&limit=15&str1=foo&str2=bar');

        $this->client->request('GET', '/statistics');

        $data = json_decode($this->client->getResponse()->getContent(), true);

        // A reached its (tied) score of 1 before B did, so A should win the tie
        self::assertSame(3, $data['parameters']['int1']);
        self::assertSame(5, $data['parameters']['int2']);
        self::assertSame(1, $data['hits']);
    }
}
