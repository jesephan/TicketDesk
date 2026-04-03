<?php

declare(strict_types=1);

namespace App\Modules\Services;

use App\Models\User;
use App\Models\Ticket;
use App\Jobs\GenerateTicketSummaryJob;
use App\Modules\Helpers\EscalationHelper;
use Illuminate\Contracts\Pagination\Paginator;

final class TicketService
{
    private $maxPerPage = 500;

    public function list(User $user, array $filters = [], int $perPage = 15): Paginator
    {
        if (! $perPage || $perPage > $this->maxPerPage) {
            $perPage = $this->maxPerPage;
        }

        return Ticket::query()
            ->with('user')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['priority'] ?? null, fn ($q, $priority) => $q->where('priority', $priority))
            ->latest()
            ->simplePaginate($perPage);
    }

    public function create(array $data): Ticket
    {
        $ticket = Ticket::create($data);

        GenerateTicketSummaryJob::dispatch($ticket);

        EscalationHelper::checkAndEscalate($ticket);

        return $ticket->fresh('user');
    }

    public function find(string $id): Ticket
    {
        return Ticket::with('user')->findOrFail($id);
    }

    public function update(Ticket $ticket, array $data): Ticket
    {
        $ticket->update($data);

        if (isset($data['description']) || isset($data['title'])) {
            GenerateTicketSummaryJob::dispatch($ticket);
        }

        EscalationHelper::checkAndEscalate($ticket);

        return $ticket->fresh('user');
    }
}
