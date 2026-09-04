<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CorsTest extends WebTestCase
{
    public function testPreflightRequestReturnsAllowedOriginHeader(): void
    {
        $client = self::createClient();

        $client->request(
            'OPTIONS',
            '/fizzbuzz',
            server: [
                'HTTP_ORIGIN' => 'http://localhost:3000',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('Access-Control-Allow-Origin');
        self::assertResponseHeaderSame('Access-Control-Allow-Origin', 'http://localhost:3000');
    }

    public function testActualRequestFromAllowedOriginIncludesCorsHeader(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz',
            server: [
                'HTTP_ORIGIN' => 'http://localhost:3000',
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('Access-Control-Allow-Origin');
    }

    public function testRequestFromDisallowedOriginHasNoCorsHeader(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz',
            server: [
                'HTTP_ORIGIN' => 'https://fake.host.com',
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertResponseNotHasHeader('Access-Control-Allow-Origin');
    }

    public function testPreflightAllowsDeleteMethodAndResetTokenHeader(): void
    {
        $client = self::createClient();

        $client->request(
            'OPTIONS',
            '/statistics',
            server: [
                'HTTP_ORIGIN' => 'http://localhost:3000',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'DELETE',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'X-Reset-Token',
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('Access-Control-Allow-Methods');
        self::assertStringContainsString('DELETE', $client->getResponse()->headers->get('Access-Control-Allow-Methods'));
    }
}
