<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
final class TicketFactory extends Factory
{
    public function definition(): array
    {
        $priority = fake()->randomElement(['low', 'medium', 'high', 'critical']);
        $category = fake()->randomElement(['bug', 'feature', 'improvement', 'support']);
        $status = fake()->randomElement(['open', 'in_progress', 'resolved', 'closed']);

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(2, true),
            'priority' => $priority,
            'category' => $category,
            'status' => $status,
            'ai_summary' => fake()->sentence(10),
            'ai_suggested_action' => fake()->sentence(8),
            'escalated' => $priority === 'critical',
            'due_date' => fake()->optional(0.5)->dateTimeBetween('now', '+2 weeks'),
        ];
    }
}
