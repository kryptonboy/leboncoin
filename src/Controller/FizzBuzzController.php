<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\FizzBuzzRequest;
use App\Service\FizzBuzzSequencer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FizzBuzzController extends AbstractController
{
    public function __construct(
        private readonly FizzBuzzSequencer $fizzBuzzSequencer,
        private readonly ValidatorInterface $validator,
        #[Target('fizzbuzz')]
        private readonly RateLimiterFactoryInterface $rateLimiter,
    ) {
    }

    #[Route('/fizzbuzz', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $limiter = $this->rateLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(
                ['error' => 'Too many requests.'],
                Response::HTTP_TOO_MANY_REQUESTS,
            );
        }

        $params = $this->extractParams($request);

        $fizzBuzzRequest = FizzBuzzRequest::fromParams($params);
        $violations = $this->validator->validate($fizzBuzzRequest);

        if (count($violations) > 0) {
            return $this->json(
                ['errors' => $this->formatViolations($violations)],
                Response::HTTP_BAD_REQUEST,
            );
        }

        // Stores the FizzBuzzRequest to retrieve it for statistics storage without having to parse the parameters again
        // see https://symfony.com/doc/current/components/http_foundation.html#component-foundation-attributes
        $request->attributes->set('fizzbuzz_request', $fizzBuzzRequest);

        return $this->json([
            'result' => $this->fizzBuzzSequencer->generate($fizzBuzzRequest),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractParams(Request $request): array
    {
        if (!$request->isMethod('POST')) {
            return $request->query->all();
        }

        // To handle valid JSON that could be posted but is not an array or an object
        $decoded = json_decode($request->getContent(), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, string>
     */
    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = (string) $violation->getMessage();
        }

        return $errors;
    }
}
