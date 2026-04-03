<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ticket;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Modules\Services\TicketService;

final class IndexTicketController extends Controller
{
    public function __invoke(Request $request, TicketService $ticketService)
    {
        $tickets = $ticketService->list(
            $request->user(),
            $request->only(['status', 'category', 'priority']),
            (int) $request->input('per_page', 15),
        );

        if ($request->wantsJson()) {
            return TicketResource::collection($tickets);
        }

        return view('tickets.index', compact('tickets'));
    }
}
