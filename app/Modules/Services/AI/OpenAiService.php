<?php

declare(strict_types=1);

namespace App\Modules\Services\AI;

use OpenAI;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

final class OpenAiService implements AiServiceInterface
{
    public function __construct(
        private FallbackAiService $fallback,
    ) {}

    public function generateSummaryAndAction(Ticket $ticket): array
    {
        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            return $this->fallback->generateSummaryAndAction($ticket);
        }

        try {
            $client = OpenAI::client($apiKey);

            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'max_tokens' => 256,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a support ticket analyzer. Respond in JSON format only: {"summary": "...", "suggested_action": "..."}'],
                    ['role' => 'user', 'content' => $this->buildPrompt($ticket)],
                ],
            ]);

            $content = $response->choices[0]->message->content;
            $parsed = json_decode($content, true);

            if ($parsed && isset($parsed['summary'], $parsed['suggested_action'])) {
                return [
                    'summary' => $parsed['summary'],
                    'suggested_action' => $parsed['suggested_action'],
                ];
            }

            return $this->fallback->generateSummaryAndAction($ticket);
        } catch (\Throwable $e) {
            Log::warning('OpenAI API failed, using fallback', ['error' => $e->getMessage()]);

            return $this->fallback->generateSummaryAndAction($ticket);
        }
    }

    private function buildPrompt(Ticket $ticket): string
    {
        return <<<PROMPT
        Analyze this support ticket and provide:
        1. A short summary (1-2 sentences max)
        2. A suggested next action (1 sentence)

        Title: {$ticket->title}
        Description: {$ticket->description}
        Priority: {$ticket->priority}
        Category: {$ticket->category}
        Status: {$ticket->status}
        PROMPT;
    }
}
