<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Modules\Helpers\EscalationHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class EscalationHelperTest extends TestCase
{
    use RefreshDatabase;

    private function makeTicket(array $attributes = []): Ticket
    {
        return Ticket::factory()->for(User::factory())->create($attributes);
    }

    // shouldEscalate tests

    public function test_should_escalate_when_priority_is_critical(): void
    {
        $ticket = $this->makeTicket(['priority' => 'critical']);

        $this->assertTrue(EscalationHelper::shouldEscalate($ticket));
    }

    public function test_should_escalate_when_high_priority_and_past_due(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'high',
            'due_date' => now()->subDay(),
        ]);

        $this->assertTrue(EscalationHelper::shouldEscalate($ticket));
    }

    public function test_should_not_escalate_when_high_priority_and_future_due(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'high',
            'due_date' => now()->addWeek(),
            'status' => 'in_progress',
        ]);

        $this->assertFalse(EscalationHelper::shouldEscalate($ticket));
    }

    public function test_should_escalate_when_open_more_than_3_days(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'medium',
            'status' => 'open',
            'created_at' => now()->subDays(4),
        ]);

        $this->assertTrue(EscalationHelper::shouldEscalate($ticket));
    }

    public function test_should_not_escalate_when_open_less_than_3_days(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'medium',
            'status' => 'open',
            'created_at' => now()->subDays(2),
        ]);

        $this->assertFalse(EscalationHelper::shouldEscalate($ticket));
    }

    public function test_should_not_escalate_low_priority_resolved_ticket(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'low',
            'status' => 'resolved',
        ]);

        $this->assertFalse(EscalationHelper::shouldEscalate($ticket));
    }

    // checkAndEscalate tests

    public function test_escalates_critical_ticket(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'critical',
            'escalated' => false,
        ]);

        EscalationHelper::checkAndEscalate($ticket);

        $this->assertTrue($ticket->fresh()->escalated);
    }

    public function test_does_not_double_escalate(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'critical',
            'escalated' => true,
        ]);

        EscalationHelper::checkAndEscalate($ticket);

        $this->assertTrue($ticket->fresh()->escalated);
    }

    public function test_does_not_escalate_low_priority(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'low',
            'status' => 'in_progress',
            'escalated' => false,
        ]);

        EscalationHelper::checkAndEscalate($ticket);

        $this->assertFalse($ticket->fresh()->escalated);
    }

    public function test_escalates_overdue_high_priority(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'high',
            'due_date' => now()->subDays(2),
            'escalated' => false,
        ]);

        EscalationHelper::checkAndEscalate($ticket);

        $this->assertTrue($ticket->fresh()->escalated);
    }
}
