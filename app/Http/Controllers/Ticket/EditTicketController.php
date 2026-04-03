<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ticket;

use App\Models\Ticket;
use App\Http\Controllers\Controller;

final class EditTicketController extends Controller
{
    public function __invoke(Ticket $ticket)
    {
        return view('tickets.edit', compact('ticket'));
    }
}
