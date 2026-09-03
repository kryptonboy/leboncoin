<?php

declare(strict_types=1);

namespace App\Service\StatisticsTracker;

use App\Dto\FizzBuzzRequest;
use App\Dto\FizzBuzzStatistic;

final class RedisStatisticsTracker implements StatisticsTrackerInterface
{
    private const SCORES_KEY = 'fizzbuzz:scores';
    private const PARAMS_KEY = 'fizzbuzz:params';
    private const LAST_HIT_KEY = 'fizzbuzz:last_hit';

    public function __construct(
        private readonly \Redis $redis,
    ) {
    }

    public function recordHit(FizzBuzzRequest $fizzBuzzRequest): void
    {
        $key = $fizzBuzzRequest->getKey();

        $this->redis->zIncrBy(self::SCORES_KEY, 1, $key);
        $this->redis->hSetNx(
            self::PARAMS_KEY,
            $key,
            json_encode(value: $fizzBuzzRequest->toArray(), flags: JSON_THROW_ON_ERROR)
        );
        $this->redis->hSet(self::LAST_HIT_KEY, $key, sprintf('%.6f', microtime(true)));
    }

    public function getMostFrequent(): ?FizzBuzzStatistic
    {
        $top = $this->redis->zRevRange(self::SCORES_KEY, 0, 0, true);

        if (empty($top)) {
            return null;
        }

        // Retrieve the best score to check if there is a tie
        $topKey = array_key_first($top);
        $maxScore = $top[$topKey];

        // Retrieve potentially tied scores
        $tiedKeys = $this->redis->zRangeByScore(
            self::SCORES_KEY,
            (string) $maxScore,
            (string) $maxScore
        );
        if (count($tiedKeys) > 1) {
            $timestamps = $this->redis->hMget(self::LAST_HIT_KEY, $tiedKeys);
            asort($timestamps);
            $topKey = array_key_first($timestamps);
        }

        $params = json_decode(
            $this->redis->hGet(self::PARAMS_KEY, (string) $topKey),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        return new FizzBuzzStatistic(
            fizzBuzzRequest: FizzBuzzRequest::fromParams($params),
            hits: (int) $maxScore,
        );
    }
}
