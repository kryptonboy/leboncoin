<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class RateLimiterTest extends WebTestCase
{
    protected function setUp(): void
    {
        // Override the limit for this test only
        putenv('RATE_LIMIT_MAX_REQUESTS=3');
        $_ENV['RATE_LIMIT_MAX_REQUESTS'] = '3';
        $_SERVER['RATE_LIMIT_MAX_REQUESTS'] = '3';
    }

    protected function tearDown(): void
    {
        self::getContainer()->get('fizzbuzz.rate_limiter.cache')->clear();
        putenv('RATE_LIMIT_MAX_REQUESTS');
        parent::tearDown();
    }

    public function testRateLimiterBlocksAfterTooManyRequests(): void
    {
        $client = self::createClient();
        self::getContainer()->get('fizzbuzz.rate_limiter.cache')->clear();

        for ($i = 0; $i < 3; ++$i) {
            $client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz');
            self::assertResponseIsSuccessful();
        }

        $client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz');
        self::assertResponseStatusCodeSame(Response::HTTP_TOO_MANY_REQUESTS);
    }
}
