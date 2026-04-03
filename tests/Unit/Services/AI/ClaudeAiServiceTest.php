<?php

declare(strict_types=1);

namespace Tests\Unit\Services\AI;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Modules\Services\AI\ClaudeAiService;
use App\Modules\Services\AI\AiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ClaudeAiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_implements_ai_service_interface(): void
    {
        $service = app(ClaudeAiService::class);

        $this->assertInstanceOf(AiServiceInterface::class, $service);
    }

    public function test_uses_fallback_when_no_api_key(): void
    {
        config(['services.anthropic.api_key' => null]);

        $service = app(ClaudeAiService::class);

        $ticket = Ticket::factory()->for(User::factory())->create([
            'priority' => 'high',
            'category' => 'bug',
        ]);

        $result = $service->generateSummaryAndAction($ticket);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('suggested_action', $result);
        $this->assertStringContainsString('High', $result['summary']);
    }

    public function test_uses_fallback_when_api_throws_exception(): void
    {
        config(['services.anthropic.api_key' => 'fake-key']);

        $service = app(ClaudeAiService::class);

        $ticket = Ticket::factory()->for(User::factory())->create();
        $result = $service->generateSummaryAndAction($ticket);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('suggested_action', $result);
        $this->assertNotEmpty($result['summary']);
    }

    public function test_returns_array_with_expected_keys(): void
    {
        config(['services.anthropic.api_key' => null]);

        $service = app(ClaudeAiService::class);
        $ticket = Ticket::factory()->for(User::factory())->create();

        $result = $service->generateSummaryAndAction($ticket);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('suggested_action', $result);
        $this->assertNotEmpty($result['summary']);
        $this->assertNotEmpty($result['suggested_action']);
    }
}
