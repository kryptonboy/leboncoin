<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class FizzBuzzRequest
{
    public function __construct(
        public int $int1,
        public int $int2,
        public int $limit,
        public string $str1,
        public string $str2,
    ) {}
}
