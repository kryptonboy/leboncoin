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
            new Assert\NotIdenticalTo(propertyPath: 'int1', message: 'This value should not be identical to int1.'),
        ])]
        public int $int2,
        #[Assert\Positive]
        public int $limit,
        #[Assert\NotBlank]
        public string $str1,
        #[Assert\NotBlank]
        public string $str2,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
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

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'int1' => $this->int1,
            'int2' => $this->int2,
            'limit' => $this->limit,
            'str1' => $this->str1,
            'str2' => $this->str2,
        ];
    }

    public function getKey(): string
    {
        return hash('sha256', json_encode($this->toArray(), JSON_THROW_ON_ERROR));
    }
}
