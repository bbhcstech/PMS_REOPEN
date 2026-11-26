<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\JsonResponse;

class AiProviderUnavailable extends HttpException
{
    public function __construct(public readonly string $reason, public readonly bool $retryable = false)
    {
        parent::__construct(503, match ($reason) {
            'missing_key' => 'The AI service needs to be configured by your platform administrator.',
            'invalid_key' => 'The AI service rejected this server’s credential. Please contact your platform administrator.',
            'billing' => 'The AI service account needs attention from your platform administrator.',
            'model_access' => 'This server cannot access its configured AI models. Please contact your platform administrator.',
            'rate_limit' => 'The AI service is busy right now. Please retry in a minute.',
            default => 'The AI service could not complete your request. You can retry your question.',
        });
    }

    public function response(array $suggestions): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(), 'retryable' => $this->retryable,
            'suggestions' => array_slice($suggestions, 0, 3),
        ], $this->getStatusCode())->header('Cache-Control', 'private, no-store');
    }
}
