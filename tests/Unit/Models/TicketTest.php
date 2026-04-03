<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class TicketTest extends TestCase
{
    use RefreshDatabase;

    private function makeTicket(array $attributes = []): Ticket
    {
        return Ticket::factory()->for(User::factory())->create($attributes);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($user)->create();

        $this->assertTrue($ticket->user->is($user));
    }

    public function test_casts_escalated_to_boolean(): void
    {
        $ticket = $this->makeTicket(['escalated' => 1]);

        $this->assertIsBool($ticket->escalated);
        $this->assertTrue($ticket->escalated);
    }

    public function test_casts_due_date_to_datetime(): void
    {
        $ticket = $this->makeTicket(['due_date' => '2026-05-01']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $ticket->due_date);
    }
}
