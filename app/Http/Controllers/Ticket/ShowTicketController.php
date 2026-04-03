<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ticket;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Modules\Services\TicketService;

final class ShowTicketController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket, TicketService $ticketService)
    {
        $ticket = $ticketService->find($ticket->id);

        if ($request->wantsJson()) {
            return new TicketResource($ticket);
        }

        return view('tickets.show', compact('ticket'));
    }
}
