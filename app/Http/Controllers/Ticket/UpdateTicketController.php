<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ticket;

use App\Models\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Modules\Services\TicketService;
use App\Http\Requests\UpdateTicketRequest;

final class UpdateTicketController extends Controller
{
    public function __invoke(UpdateTicketRequest $request, Ticket $ticket, TicketService $ticketService)
    {
        $ticket = $ticketService->update($ticket, $request->validated());

        if ($request->wantsJson()) {
            return new TicketResource($ticket);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully.');
    }
}
