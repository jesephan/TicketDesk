<?php

declare(strict_types=1);

namespace Tests\Feature\Tickets;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Queue;
use App\Jobs\GenerateTicketSummaryJob;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class TicketCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Queue::fake();
    }

    public function test_index_displays_tickets(): void
    {
        Ticket::factory(3)->for($this->user)->create();

        $this->actingAs($this->user)
            ->get('/')
            ->assertStatus(200)
            ->assertViewHas('tickets');
    }

    public function test_index_returns_json_when_requested(): void
    {
        Ticket::factory(3)->for($this->user)->create();

        $this->actingAs($this->user)
            ->getJson('/')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'priority', 'category', 'status', 'ai_summary', 'ai_suggested_action', 'escalated'],
                ],
            ]);
    }

    public function test_create_page_is_accessible(): void
    {
        $this->actingAs($this->user)
            ->get('/tickets/create')
            ->assertStatus(200);
    }

    public function test_can_store_a_ticket(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'title' => 'Test Ticket',
                'description' => 'This is a test ticket with enough description.',
                'priority' => 'high',
                'category' => 'bug',
            ])
            ->assertRedirect(route('tickets.index'));

        $this->assertDatabaseHas('tickets', [
            'title' => 'Test Ticket',
            'priority' => 'high',
            'category' => 'bug',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_store_dispatches_ai_summary_job(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'title' => 'AI Test Ticket',
                'description' => 'Description long enough to be valid.',
                'priority' => 'medium',
                'category' => 'support',
            ]);

        Queue::assertPushed(GenerateTicketSummaryJob::class, function ($job) {
            return $job->ticket->title === 'AI Test Ticket';
        });
    }

    public function test_store_returns_json_when_requested(): void
    {
        $this->actingAs($this->user)
            ->postJson('/tickets', [
                'title' => 'JSON Ticket',
                'description' => 'This is a JSON test ticket description.',
                'priority' => 'low',
                'category' => 'feature',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'JSON Ticket']);
    }

    public function test_can_view_a_ticket(): void
    {
        $ticket = Ticket::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->get("/tickets/{$ticket->id}")
            ->assertStatus(200)
            ->assertSee($ticket->title);
    }

    public function test_can_view_ticket_as_json(): void
    {
        $ticket = Ticket::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->getJson("/tickets/{$ticket->id}")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $ticket->id]);
    }

    public function test_edit_page_is_accessible(): void
    {
        $ticket = Ticket::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->get("/tickets/{$ticket->id}/edit")
            ->assertStatus(200);
    }

    public function test_can_update_a_ticket(): void
    {
        $ticket = Ticket::factory()->for($this->user)->create([
            'status' => 'open',
        ]);

        $this->actingAs($this->user)
            ->put("/tickets/{$ticket->id}", [
                'title' => $ticket->title,
                'description' => $ticket->description,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
                'status' => 'in_progress',
            ])
            ->assertRedirect(route('tickets.index'));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_update_dispatches_ai_job_when_description_changes(): void
    {
        $ticket = Ticket::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->putJson("/tickets/{$ticket->id}", [
                'description' => 'Updated description that is long enough.',
            ]);

        Queue::assertPushed(GenerateTicketSummaryJob::class, function ($job) use ($ticket) {
            return $job->ticket->id === $ticket->id;
        });
    }

    public function test_update_does_not_dispatch_ai_job_for_status_only_change(): void
    {
        $ticket = Ticket::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->putJson("/tickets/{$ticket->id}", [
                'status' => 'resolved',
            ]);

        Queue::assertNotPushed(GenerateTicketSummaryJob::class);
    }

    public function test_update_returns_json_when_requested(): void
    {
        $ticket = Ticket::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->putJson("/tickets/{$ticket->id}", [
                'status' => 'resolved',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'resolved']);
    }

    public function test_returns_404_for_nonexistent_ticket(): void
    {
        $this->actingAs($this->user)
            ->get('/tickets/99999')
            ->assertStatus(404);
    }
}
