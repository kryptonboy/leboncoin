<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Dto\FizzBuzzRequest;
use App\Service\StatisticsTracker\StatisticsTrackerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::TERMINATE)]
final readonly class StatisticsListener
{
    public function __construct(
        private StatisticsTrackerInterface $statisticsTracker,
    ) {}

    public function __invoke(TerminateEvent $event): void
    {
        $fizzBuzzRequest = $event->getRequest()->attributes->get('fizzbuzz_request');

        if (!$fizzBuzzRequest instanceof FizzBuzzRequest) {
            return;
        }

        $this->statisticsTracker->recordHit($fizzBuzzRequest);
    }
}
