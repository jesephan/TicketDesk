<?php

declare(strict_types=1);

namespace Tests\Unit\Services\AI;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Modules\Services\AI\OpenAiService;
use App\Modules\Services\AI\AiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class OpenAiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_implements_ai_service_interface(): void
    {
        $service = app(OpenAiService::class);

        $this->assertInstanceOf(AiServiceInterface::class, $service);
    }

    public function test_uses_fallback_when_no_api_key(): void
    {
        config(['services.openai.api_key' => null]);

        $service = app(OpenAiService::class);

        $ticket = Ticket::factory()->for(User::factory())->create([
            'priority' => 'critical',
            'category' => 'support',
        ]);

        $result = $service->generateSummaryAndAction($ticket);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('suggested_action', $result);
        $this->assertStringContainsString('Critical', $result['summary']);
    }

    public function test_uses_fallback_when_api_throws_exception(): void
    {
        config(['services.openai.api_key' => 'fake-key']);

        $service = app(OpenAiService::class);

        $ticket = Ticket::factory()->for(User::factory())->create();
        $result = $service->generateSummaryAndAction($ticket);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('suggested_action', $result);
        $this->assertNotEmpty($result['summary']);
    }

    public function test_returns_array_with_expected_keys(): void
    {
        config(['services.openai.api_key' => null]);

        $service = app(OpenAiService::class);
        $ticket = Ticket::factory()->for(User::factory())->create();

        $result = $service->generateSummaryAndAction($ticket);

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('suggested_action', $result);
        $this->assertNotEmpty($result['summary']);
        $this->assertNotEmpty($result['suggested_action']);
    }
}
