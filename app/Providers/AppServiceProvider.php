<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Services\AI\OpenAiService;
use App\Modules\Services\AI\ClaudeAiService;
use App\Modules\Services\AI\FallbackAiService;
use App\Modules\Services\AI\AiServiceInterface;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AiServiceInterface::class, function () {
            return match (config('services.ai.provider')) {
                'claude' => app(ClaudeAiService::class),
                'openai' => app(OpenAiService::class),
                default => app(FallbackAiService::class),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
