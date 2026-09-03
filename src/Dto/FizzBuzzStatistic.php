<?php

declare(strict_types=1);

namespace App\Dto;

final class FizzBuzzStatistic
{
    public function __construct(
        public FizzBuzzRequest $fizzBuzzRequest,
        public int $hits,
    ) {}
}
