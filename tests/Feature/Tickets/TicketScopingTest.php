<?php

declare(strict_types=1);

namespace Tests\Feature\Tickets;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class TicketScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_all_tickets(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $otherUser = User::factory()->create();

        Ticket::factory(3)->for($admin)->create();
        Ticket::factory(2)->for($otherUser)->create();

        $response = $this->actingAs($admin)->getJson('/');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_regular_user_sees_only_own_tickets(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Ticket::factory(3)->for($user)->create();
        Ticket::factory(2)->for($otherUser)->create();

        $response = $this->actingAs($user)->getJson('/');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_regular_user_does_not_see_others_tickets(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Ticket::factory(5)->for($otherUser)->create();

        $response = $this->actingAs($user)->getJson('/');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_super_admin_sees_all_with_filters(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $otherUser = User::factory()->create();

        Ticket::factory()->for($admin)->create(['status' => 'open']);
        Ticket::factory()->for($otherUser)->create(['status' => 'open']);
        Ticket::factory()->for($otherUser)->create(['status' => 'closed']);

        $response = $this->actingAs($admin)->getJson('/?status=open');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_regular_user_filter_scoped_to_own_tickets(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Ticket::factory()->for($user)->create(['status' => 'open']);
        Ticket::factory()->for($otherUser)->create(['status' => 'open']);

        $response = $this->actingAs($user)->getJson('/?status=open');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}
