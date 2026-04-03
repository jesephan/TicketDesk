<?php

declare(strict_types=1);

namespace App\Modules\Services\AI;

use App\Models\Ticket;
use Illuminate\Support\Str;

final class FallbackAiService implements AiServiceInterface
{
    public function generateSummaryAndAction(Ticket $ticket): array
    {
        $summary = sprintf(
            '%s %s ticket: %s',
            ucfirst($ticket->priority),
            $ticket->category,
            Str::limit($ticket->description, 100),
        );

        $priorityActions = config('ticket.fallback_actions.priority', []);
        $categoryPrefixes = config('ticket.fallback_actions.category_prefix', []);

        $suggestedAction = $priorityActions[$ticket->priority] ?? 'Review and triage this ticket.';

        if (isset($categoryPrefixes[$ticket->category])) {
            $suggestedAction = $categoryPrefixes[$ticket->category].' '.$suggestedAction;
        }

        return [
            'summary' => $summary,
            'suggested_action' => $suggestedAction,
        ];
    }
}
