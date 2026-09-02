<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FizzBuzzRequest
{
    public function __construct(
        #[Assert\Positive]
        public int $int1,
        #[Assert\Sequentially([
            new Assert\Positive(),
            new Assert\NotIdenticalTo(propertyPath: "int1", message: "This value should not be identical to int1."),
        ])]
        public int $int2,
        #[Assert\Positive]
        public int $limit,
        #[Assert\NotBlank]
        public string $str1,
        #[Assert\NotBlank]
        public string $str2,
    ) {}

    public static function fromParams(array $params): self
    {
        return new self(
            int1: (int) ($params['int1'] ?? 0),
            int2: (int) ($params['int2'] ?? 0),
            limit: (int) ($params['limit'] ?? 0),
            str1: (string) ($params['str1'] ?? ''),
            str2: (string) ($params['str2'] ?? ''),
        );
    }
}
