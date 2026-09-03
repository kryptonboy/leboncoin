<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\StatisticsTracker\StatisticsTrackerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class StatisticsController extends AbstractController
{
    public function __construct(
        private readonly StatisticsTrackerInterface $statisticsTracker,
    ) {
    }

    #[Route('/statistics', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $statistic = $this->statisticsTracker->getMostFrequent();

        if (null === $statistic) {
            return $this->json([
                'parameters' => null,
                'hits' => 0,
            ]);
        }

        return $this->json([
            'parameters' => $statistic->fizzBuzzRequest->toArray(),
            'hits' => $statistic->hits,
        ]);
    }
}
