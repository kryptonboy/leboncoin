<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Dto\FizzBuzzRequest;
use App\Service\FizzBuzzSequencer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FizzBuzzSequencerTest extends TestCase
{
    private const DEFAULT_INT1 = 3;
    private const DEFAULT_INT2 = 5;
    private const DEFAULT_STR1 = 'fizz';
    private const DEFAULT_STR2 = 'buzz';

    private FizzBuzzSequencer $fizzBuzzSequencer;

    /**
     * @return iterable<string, array<string, int>>
     */
    public static function provideLimitValues(): iterable
    {
        yield 'Limit to 5' => ['limitValue' => 5];
        yield 'Limit to 10' => ['limitValue' => 10];
        yield 'Limit to 50' => ['limitValue' => 50];
        yield 'Limit to 100' => ['limitValue' => 100];
    }

    /**
     * @return iterable<string, array<string, mixed>>
     */
    public static function provideCustomConfigurations(): iterable
    {
        yield 'Foo everywhere, Bar somewhere' => [
            'int1' => 1,
            'int2' => 3,
            'limit' => 5,
            'str1' => 'foo',
            'str2' => 'bar',
            'expected' => [
                'foo',
                'foo',
                'foobar',
                'foo',
                'foo',
            ],
        ];
        yield 'No fizz, no buzz' => [
            'int1' => 7,
            'int2' => 8,
            'limit' => 6,
            'str1' => 'fizz',
            'str2' => 'buzz',
            'expected' => [
                '1',
                '2',
                '3',
                '4',
                '5',
                '6',
            ],
        ];
        yield 'Confusing' => [
            'int1' => 2,
            'int2' => 3,
            'limit' => 6,
            'str1' => '3',
            'str2' => '2',
            'expected' => [
                '1',
                '3',
                '2',
                '3',
                '5',
                '32',
            ],
        ];
    }

    public function testDefaultFizzBuzzWithLimitToFifteen(): void
    {
        $request = $this->buildFizzBuzzRequest(limit: 15);

        $generatedSequence = $this->fizzBuzzSequencer->generate($request);

        $expectedSequence = [
            '1',
            '2',
            'fizz',
            '4',
            'buzz',
            'fizz',
            '7',
            '8',
            'fizz',
            'buzz',
            '11',
            'fizz',
            '13',
            '14',
            'fizzbuzz',
        ];
        $this->assertSame($expectedSequence, $generatedSequence);
    }

    private function buildFizzBuzzRequest(
        int $limit,
        int $int1 = self::DEFAULT_INT1,
        int $int2 = self::DEFAULT_INT2,
        string $str1 = self::DEFAULT_STR1,
        string $str2 = self::DEFAULT_STR2,
    ): FizzBuzzRequest {
        return new FizzBuzzRequest(
            int1: $int1,
            int2: $int2,
            limit: $limit,
            str1: $str1,
            str2: $str2,
        );
    }

    #[DataProvider('provideLimitValues')]
    public function testResultLengthMatchesLimitValue(int $limitValue): void
    {
        $request = $this->buildFizzBuzzRequest(limit: $limitValue);
        $result = $this->fizzBuzzSequencer->generate($request);
        $this->assertCount($limitValue, $result);
    }

    /**
     * @param array<string, string> $expected
     */
    #[DataProvider('provideCustomConfigurations')]
    public function testWithCustomConfigurations(
        int $limit,
        int $int1,
        int $int2,
        string $str1,
        string $str2,
        array $expected,
    ): void {
        $request = $this->buildFizzBuzzRequest(int1: $int1, int2: $int2, limit: $limit, str1: $str1, str2: $str2);
        $result = $this->fizzBuzzSequencer->generate($request);
        $this->assertSame($expected, $result);
    }

    protected function setUp(): void
    {
        $this->fizzBuzzSequencer = new FizzBuzzSequencer();
    }
}
