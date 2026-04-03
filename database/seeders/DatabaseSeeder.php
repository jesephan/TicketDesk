<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Default login user (password: "password")
        $admin = User::factory()->superAdmin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $users = User::factory(3)->create();
        $allUsers = $users->push($admin);

        // Sample tickets
        $sampleTickets = [
            [
                'title' => 'Login page returns 500 error',
                'description' => 'When users attempt to log in with valid credentials, the server returns a 500 Internal Server Error. This started after the latest deployment. Affects all users trying to authenticate.',
                'priority' => 'critical',
                'category' => 'bug',
                'status' => 'open',
                'escalated' => true,
                'ai_summary' => 'Critical authentication failure causing 500 errors post-deployment.',
                'ai_suggested_action' => 'Check recent deployment changes and server logs for authentication middleware errors.',
            ],
            [
                'title' => 'Add dark mode support',
                'description' => 'Users have requested dark mode support for the application. This would help reduce eye strain during nighttime usage and align with modern UI standards.',
                'priority' => 'low',
                'category' => 'feature',
                'status' => 'open',
                'ai_summary' => 'Feature request for dark mode to improve UX during nighttime usage.',
                'ai_suggested_action' => 'Add to backlog. Review when capacity allows.',
            ],
            [
                'title' => 'Dashboard loading slowly',
                'description' => 'The main dashboard takes over 10 seconds to load. Database queries seem to be the bottleneck. Users are complaining about productivity impact.',
                'priority' => 'high',
                'category' => 'improvement',
                'status' => 'in_progress',
                'due_date' => now()->addDays(2),
                'ai_summary' => 'Performance issue: dashboard loads in 10+ seconds due to slow DB queries.',
                'ai_suggested_action' => 'Profile slow queries, add database indexes, and consider caching.',
            ],
            [
                'title' => 'How to export reports to PDF',
                'description' => 'A customer is asking how to export their monthly reports to PDF format. They need this for their quarterly review meeting next week.',
                'priority' => 'medium',
                'category' => 'support',
                'status' => 'open',
                'ai_summary' => 'Customer needs guidance on PDF export for upcoming quarterly review.',
                'ai_suggested_action' => 'Provide documentation link for PDF export feature. Follow up within 3 days.',
            ],
            [
                'title' => 'Upgrade payment gateway to v3',
                'description' => 'The current payment gateway SDK (v2) is being deprecated in 60 days. We need to migrate to v3 which has breaking API changes in the webhook handling.',
                'priority' => 'high',
                'category' => 'improvement',
                'status' => 'open',
                'due_date' => now()->subDay(),
                'escalated' => true,
                'ai_summary' => 'Urgent SDK migration needed before v2 deprecation in 60 days.',
                'ai_suggested_action' => 'Prioritize in current sprint. Review v3 migration guide and plan webhook refactor.',
            ],
        ];

        foreach ($sampleTickets as $ticketData) {
            Ticket::create([
                ...$ticketData,
                'user_id' => $allUsers->random()->id,
            ]);
        }

        // Add some random tickets
        Ticket::factory(10)->recycle($allUsers)->create();
    }
}
