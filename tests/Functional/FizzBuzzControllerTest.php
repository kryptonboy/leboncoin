<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class FizzBuzzControllerTest extends WebTestCase
{
    /**
     * @return iterable<string, array{params: array<string, mixed>, expectedSequence: array<int, string>}>
     */
    public static function provideValidParams(): iterable
    {
        yield 'Default params' => [
            'params' => ['int1' => 3, 'int2' => 5, 'limit' => 15, 'str1' => 'fizz', 'str2' => 'buzz'],
            'expectedSequence' => ['1', '2', 'fizz', '4', 'buzz', 'fizz', '7', '8', 'fizz', 'buzz', '11', 'fizz', '13', '14', 'fizzbuzz'],
        ];

        yield 'Many foos few bars' => [
            'params' => ['int1' => 2, 'int2' => 7, 'limit' => 15, 'str1' => 'foo', 'str2' => 'bar'],
            'expectedSequence' => ['1', 'foo', '3', 'foo', '5', 'foo', 'bar', 'foo', '9', 'foo', '11', 'foo', '13', 'foobar', '15'],
        ];
    }

    /**
     * @return iterable<string, array{params: array<string, mixed>, errors: array<string, string>}>
     */
    public static function provideInvalidParams(): iterable
    {
        yield 'int1 not positive' => [
            'params' => ['int1' => 0, 'int2' => 5, 'limit' => 15, 'str1' => 'fizz', 'str2' => 'buzz'],
            'errors' => ['int1' => 'This value should be positive.'],
        ];
        yield 'int2 not positive' => [
            'params' => ['int1' => 3, 'int2' => 0, 'limit' => 15, 'str1' => 'fizz', 'str2' => 'buzz'],
            'errors' => ['int2' => 'This value should be positive.'],
        ];
        yield 'limit not positive' => [
            'params' => ['int1' => 3, 'int2' => 5, 'limit' => -1, 'str1' => 'fizz', 'str2' => 'buzz'],
            'errors' => ['limit' => 'This value should be positive.'],
        ];
        yield 'int2 identical to int1' => [
            'params' => ['int1' => 3, 'int2' => 3, 'limit' => 15, 'str1' => 'fizz', 'str2' => 'buzz'],
            'errors' => ['int2' => 'This value should not be identical to int1.'],
        ];
        yield 'str1 blank' => [
            'params' => ['int1' => 3, 'int2' => 5, 'limit' => 15, 'str1' => '', 'str2' => 'buzz'],
            'errors' => ['str1' => 'This value should not be blank.'],
        ];
        yield 'str2 blank' => [
            'params' => ['int1' => 3, 'int2' => 5, 'limit' => 15, 'str1' => 'fizz', 'str2' => ''],
            'errors' => ['str2' => 'This value should not be blank.'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @param array<int, string>   $expectedSequence
     */
    #[DataProvider('provideValidParams')]
    public function testGetFizzBuzzReturnsExpectedSequence(array $params, array $expectedSequence): void
    {
        $client = self::createClient();
        $client->request('GET', '/fizzbuzz', $params);

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame(
            $expectedSequence,
            $data['result'],
        );
    }

    /**
     * @param array<string, mixed> $params
     * @param array<int, string>   $expectedSequence
     */
    #[DataProvider('provideValidParams')]
    public function testPostFizzBuzzReturnsExpectedSequence(array $params, array $expectedSequence): void
    {
        $client = self::createClient();
        $client->jsonRequest('POST', '/fizzbuzz', $params);

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame(
            $expectedSequence,
            $data['result'],
        );
    }

    public function testMissingParamsReturns400(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fizzbuzz');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertArrayHasKey('errors', $data);
        self::assertCount(5, $data['errors']);
    }

    /**
     * @param array<string, mixed>  $params
     * @param array<string, string> $errors
     */
    #[DataProvider('provideInvalidParams')]
    public function testInvalidParamsReturns400WithAccurateFieldError(array $params, array $errors): void
    {
        $client = self::createClient();
        $client->request('GET', '/fizzbuzz', $params);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame($errors, $data['errors']);
    }

    public function testMalformedJsonBodyReturns400(): void
    {
        $client = self::createClient();

        $client->request(
            method: 'POST',
            uri: '/fizzbuzz',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: 'not valid json',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
