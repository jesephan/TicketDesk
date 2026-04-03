<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Services\AI\AiServiceInterface;

final class GenerateTicketSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public Ticket $ticket,
    ) {}

    public function handle(AiServiceInterface $aiService): void
    {
        $result = $aiService->generateSummaryAndAction($this->ticket);

        $this->ticket->update([
            'ai_summary' => $result['summary'],
            'ai_suggested_action' => $result['suggested_action'],
        ]);
    }
}
