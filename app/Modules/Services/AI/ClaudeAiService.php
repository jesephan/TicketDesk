<?php

declare(strict_types=1);

namespace App\Modules\Services\AI;

use Anthropic\Client;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

final class ClaudeAiService implements AiServiceInterface
{
    public function __construct(
        private FallbackAiService $fallback,
    ) {}

    public function generateSummaryAndAction(Ticket $ticket): array
    {
        $apiKey = config('services.anthropic.api_key');

        if (! $apiKey) {
            return $this->fallback->generateSummaryAndAction($ticket);
        }

        try {
            $client = new Client(apiKey: $apiKey);

            $response = $client->messages->create(
                maxTokens: 256,
                messages: [
                    ['role' => 'user', 'content' => $this->buildPrompt($ticket)],
                ],
                model: 'claude-sonnet-4-20250514',
            );

            $content = $response->content[0]->text;
            $parsed = json_decode($content, true);

            if ($parsed && isset($parsed['summary'], $parsed['suggested_action'])) {
                return [
                    'summary' => $parsed['summary'],
                    'suggested_action' => $parsed['suggested_action'],
                ];
            }

            return $this->fallback->generateSummaryAndAction($ticket);
        } catch (\Throwable $e) {
            Log::warning('Claude API failed, using fallback', ['error' => $e->getMessage()]);

            return $this->fallback->generateSummaryAndAction($ticket);
        }
    }

    private function buildPrompt(Ticket $ticket): string
    {
        return <<<PROMPT
        You are a support ticket analyzer. Given the following ticket, provide:
        1. A short summary (1-2 sentences max)
        2. A suggested next action (1 sentence)

        Title: {$ticket->title}
        Description: {$ticket->description}
        Priority: {$ticket->priority}
        Category: {$ticket->category}
        Status: {$ticket->status}

        Respond in JSON format only:
        {"summary": "...", "suggested_action": "..."}
        PROMPT;
    }
}
