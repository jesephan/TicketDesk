<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Modules\Services\TicketService;
use App\Http\Requests\StoreTicketRequest;

final class StoreTicketController extends Controller
{
    public function __invoke(StoreTicketRequest $request, TicketService $ticketService)
    {
        $ticket = $ticketService->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        if ($request->wantsJson()) {
            return (new TicketResource($ticket))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }
}
