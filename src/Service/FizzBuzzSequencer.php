<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\FizzBuzzRequest;

class FizzBuzzSequencer
{
    /**
     * @return string[]
     */
    public function generate(FizzBuzzRequest $request): array
    {
        $fizzBuzzSequence = [];

        for ($i = 1;$i <= $request->limit;$i++) {
            $isMultipleOfInt1 = $i % $request->int1 === 0;
            $isMultipleOfInt2 = $i % $request->int2 === 0;

            $fizzBuzzSequence[] = match(true) {
                $isMultipleOfInt1 && $isMultipleOfInt2 => $request->str1 . $request->str2,
                $isMultipleOfInt1 => $request->str1,
                $isMultipleOfInt2 => $request->str2,
                default => (string) $i,
            };
        }

        return $fizzBuzzSequence;
    }
}
