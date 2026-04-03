<?php

declare(strict_types=1);

namespace Tests\Unit\Services\AI;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Modules\Services\AI\FallbackAiService;
use App\Modules\Services\AI\AiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class FallbackAiServiceTest extends TestCase
{
    use RefreshDatabase;

    private FallbackAiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FallbackAiService;
    }

    private function makeTicket(array $attributes = []): Ticket
    {
        return Ticket::factory()->for(User::factory())->create($attributes);
    }

    public function test_implements_ai_service_interface(): void
    {
        $this->assertInstanceOf(AiServiceInterface::class, $this->service);
    }

    public function test_generates_summary_with_priority_and_category(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'high',
            'category' => 'feature',
            'description' => 'We need a new dashboard widget for analytics.',
        ]);

        $result = $this->service->generateSummaryAndAction($ticket);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('suggested_action', $result);
        $this->assertStringContainsString('High', $result['summary']);
        $this->assertStringContainsString('feature', $result['summary']);
    }

    public function test_critical_priority_suggests_immediate_attention(): void
    {
        $ticket = $this->makeTicket(['priority' => 'critical', 'category' => 'support']);

        $result = $this->service->generateSummaryAndAction($ticket);

        $this->assertStringContainsString('Immediate attention', $result['suggested_action']);
    }

    public function test_high_priority_suggests_current_sprint(): void
    {
        $ticket = $this->makeTicket(['priority' => 'high', 'category' => 'feature']);

        $result = $this->service->generateSummaryAndAction($ticket);

        $this->assertStringContainsString('current sprint', $result['suggested_action']);
    }

    public function test_medium_priority_suggests_3_day_review(): void
    {
        $ticket = $this->makeTicket(['priority' => 'medium', 'category' => 'improvement']);

        $result = $this->service->generateSummaryAndAction($ticket);

        $this->assertStringContainsString('3 days', $result['suggested_action']);
    }

    public function test_low_priority_suggests_backlog(): void
    {
        $ticket = $this->makeTicket(['priority' => 'low', 'category' => 'feature']);

        $result = $this->service->generateSummaryAndAction($ticket);

        $this->assertStringContainsString('backlog', $result['suggested_action']);
    }

    public function test_bug_category_prepends_reproduce_suggestion(): void
    {
        $ticket = $this->makeTicket(['priority' => 'medium', 'category' => 'bug']);

        $result = $this->service->generateSummaryAndAction($ticket);

        $this->assertStringStartsWith('Reproduce the issue', $result['suggested_action']);
    }

    public function test_summary_truncates_long_descriptions(): void
    {
        $ticket = $this->makeTicket([
            'priority' => 'low',
            'category' => 'support',
            'description' => str_repeat('A very long description. ', 50),
        ]);

        $result = $this->service->generateSummaryAndAction($ticket);

        $this->assertLessThan(200, strlen($result['summary']));
    }
}
