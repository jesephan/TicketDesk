<?php

declare(strict_types=1);

namespace Tests\Feature\Tickets;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class TicketValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Queue::fake();
    }

    public function test_store_requires_title(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'description' => 'Valid description content here.',
                'priority' => 'medium',
                'category' => 'bug',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_store_requires_description(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'title' => 'Test',
                'priority' => 'medium',
                'category' => 'bug',
            ])
            ->assertSessionHasErrors('description');
    }

    public function test_store_requires_minimum_description_length(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'title' => 'Test',
                'description' => 'Short',
                'priority' => 'medium',
                'category' => 'bug',
            ])
            ->assertSessionHasErrors('description');
    }

    public function test_store_requires_valid_priority(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'title' => 'Test',
                'description' => 'Valid description content here.',
                'priority' => 'urgent',
                'category' => 'bug',
            ])
            ->assertSessionHasErrors('priority');
    }

    public function test_store_requires_valid_category(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'title' => 'Test',
                'description' => 'Valid description content here.',
                'priority' => 'medium',
                'category' => 'question',
            ])
            ->assertSessionHasErrors('category');
    }

    public function test_store_rejects_past_due_date(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'title' => 'Test',
                'description' => 'Valid description content here.',
                'priority' => 'medium',
                'category' => 'bug',
                'due_date' => now()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors('due_date');
    }

    public function test_store_accepts_valid_future_due_date(): void
    {
        $this->actingAs($this->user)
            ->post('/tickets', [
                'title' => 'Test Ticket',
                'description' => 'Valid description content here.',
                'priority' => 'medium',
                'category' => 'bug',
                'due_date' => now()->addWeek()->toDateString(),
            ])
            ->assertRedirect(route('tickets.index'));
    }

    public function test_update_rejects_invalid_status(): void
    {
        $ticket = \App\Models\Ticket::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->put("/tickets/{$ticket->id}", [
                'status' => 'deleted',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_json_validation_returns_422(): void
    {
        $this->actingAs($this->user)
            ->postJson('/tickets', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'priority', 'category']);
    }
}
