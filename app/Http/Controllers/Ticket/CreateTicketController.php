<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;

final class CreateTicketController extends Controller
{
    public function __invoke()
    {
        return view('tickets.create');
    }
}
