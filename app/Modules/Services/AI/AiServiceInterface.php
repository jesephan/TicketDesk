<?php

declare(strict_types=1);

namespace App\Modules\Services\AI;

use App\Models\Ticket;

interface AiServiceInterface
{
    /**
     * @return array{summary: string, suggested_action: string}
     */
    public function generateSummaryAndAction(Ticket $ticket): array;
}
