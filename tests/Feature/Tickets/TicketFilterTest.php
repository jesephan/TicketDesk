<?php

declare(strict_types=1);

namespace Tests\Feature\Tickets;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class TicketFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_filter_by_status(): void
    {
        Ticket::factory()->for($this->user)->create(['status' => 'open']);
        Ticket::factory()->for($this->user)->create(['status' => 'closed']);
        Ticket::factory()->for($this->user)->create(['status' => 'closed']);

        $response = $this->actingAs($this->user)
            ->getJson('/?status=closed');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals('closed', $response->json('data.0.status'));
    }

    public function test_filter_by_category(): void
    {
        Ticket::factory()->for($this->user)->create(['category' => 'bug']);
        Ticket::factory()->for($this->user)->create(['category' => 'feature']);

        $response = $this->actingAs($this->user)
            ->getJson('/?category=bug');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('bug', $response->json('data.0.category'));
    }

    public function test_filter_by_priority(): void
    {
        Ticket::factory()->for($this->user)->create(['priority' => 'critical']);
        Ticket::factory()->for($this->user)->create(['priority' => 'low']);
        Ticket::factory()->for($this->user)->create(['priority' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/?priority=low');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_filter_by_multiple_params(): void
    {
        Ticket::factory()->for($this->user)->create(['status' => 'open', 'priority' => 'high', 'category' => 'bug']);
        Ticket::factory()->for($this->user)->create(['status' => 'open', 'priority' => 'low', 'category' => 'bug']);
        Ticket::factory()->for($this->user)->create(['status' => 'closed', 'priority' => 'high', 'category' => 'feature']);

        $response = $this->actingAs($this->user)
            ->getJson('/?status=open&priority=high');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_no_filter_returns_all(): void
    {
        Ticket::factory(5)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_per_page_controls_result_count(): void
    {
        Ticket::factory(20)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
    }

    public function test_per_page_exceeding_max_is_capped(): void
    {
        Ticket::factory(20)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/?per_page=999');

        $response->assertStatus(200);
        $this->assertCount(20, $response->json('data'));
    }

    public function test_simple_pagination_has_next_page(): void
    {
        Ticket::factory(20)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/?per_page=10');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('links.next'));
    }
}
