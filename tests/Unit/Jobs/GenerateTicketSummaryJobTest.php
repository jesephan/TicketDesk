<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Queue;
use App\Jobs\GenerateTicketSummaryJob;
use App\Modules\Services\AI\AiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class GenerateTicketSummaryJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_updates_ticket_with_ai_summary(): void
    {
        $ticket = Ticket::factory()->for(User::factory())->create([
            'ai_summary' => null,
            'ai_suggested_action' => null,
        ]);

        $this->mock(AiServiceInterface::class, function ($mock) {
            $mock->shouldReceive('generateSummaryAndAction')
                ->once()
                ->andReturn([
                    'summary' => 'Generated summary',
                    'suggested_action' => 'Generated action',
                ]);
        });

        (new GenerateTicketSummaryJob($ticket))->handle(app(AiServiceInterface::class));

        $ticket->refresh();
        $this->assertEquals('Generated summary', $ticket->ai_summary);
        $this->assertEquals('Generated action', $ticket->ai_suggested_action);
    }

    public function test_job_is_queued(): void
    {
        Queue::fake();

        $ticket = Ticket::factory()->for(User::factory())->create();

        GenerateTicketSummaryJob::dispatch($ticket);

        Queue::assertPushed(GenerateTicketSummaryJob::class, function ($job) use ($ticket) {
            return $job->ticket->id === $ticket->id;
        });
    }

    public function test_job_implements_should_queue(): void
    {
        $ticket = Ticket::factory()->for(User::factory())->make();
        $job = new GenerateTicketSummaryJob($ticket);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }

    public function test_job_has_retry_configuration(): void
    {
        $ticket = Ticket::factory()->for(User::factory())->make();
        $job = new GenerateTicketSummaryJob($ticket);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(30, $job->backoff);
    }
}
