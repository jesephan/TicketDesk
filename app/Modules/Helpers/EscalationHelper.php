<?php

declare(strict_types=1);

namespace App\Modules\Helpers;

use App\Models\Ticket;

final class EscalationHelper
{
    public static function shouldEscalate(Ticket $ticket): bool
    {
        if ($ticket->priority === 'critical') {
            return true;
        }

        if ($ticket->priority === 'high' && $ticket->due_date?->isPast()) {
            return true;
        }

        if ($ticket->status === 'open' && $ticket->created_at->diffInDays(now()) > 3) {
            return true;
        }

        return false;
    }

    public static function checkAndEscalate(Ticket $ticket): void
    {
        if (self::shouldEscalate($ticket) && ! $ticket->escalated) {
            $ticket->update(['escalated' => true]);
        }
    }
}
