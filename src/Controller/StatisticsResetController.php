<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\StatisticsTracker\StatisticsTrackerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatisticsResetController
{
    public function __construct(
        private readonly StatisticsTrackerInterface $statisticsTracker,
        private readonly string $resetToken,
    ) {
    }

    #[Route('/statistics', methods: ['DELETE'])]
    public function __invoke(Request $request): Response
    {
        $providedToken = $request->headers->get('X-Reset-Token', '');

        if ('' === $this->resetToken || !hash_equals($this->resetToken, $providedToken)) {
            return new Response(status: Response::HTTP_FORBIDDEN);
        }

        $this->statisticsTracker->reset();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
