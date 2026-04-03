<?php

declare(strict_types=1);

namespace Tests\Unit\Services\AI;

use Tests\TestCase;
use App\Modules\Services\AI\OpenAiService;
use App\Modules\Services\AI\ClaudeAiService;
use App\Modules\Services\AI\FallbackAiService;
use App\Modules\Services\AI\AiServiceInterface;

final class AiServiceContainerTest extends TestCase
{
    public function test_resolves_claude_when_configured(): void
    {
        config(['services.ai.provider' => 'claude']);

        $service = app(AiServiceInterface::class);

        $this->assertInstanceOf(ClaudeAiService::class, $service);
    }

    public function test_resolves_openai_when_configured(): void
    {
        config(['services.ai.provider' => 'openai']);

        $service = app(AiServiceInterface::class);

        $this->assertInstanceOf(OpenAiService::class, $service);
    }

    public function test_resolves_fallback_when_provider_is_null(): void
    {
        config(['services.ai.provider' => null]);

        $service = app(AiServiceInterface::class);

        $this->assertInstanceOf(FallbackAiService::class, $service);
    }

    public function test_resolves_fallback_for_unknown_provider(): void
    {
        config(['services.ai.provider' => 'unknown']);

        $service = app(AiServiceInterface::class);

        $this->assertInstanceOf(FallbackAiService::class, $service);
    }

    public function test_resolves_fallback_when_explicitly_set(): void
    {
        config(['services.ai.provider' => 'fallback']);

        $service = app(AiServiceInterface::class);

        $this->assertInstanceOf(FallbackAiService::class, $service);
    }
}
