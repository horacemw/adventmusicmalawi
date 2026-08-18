<?php

namespace App\Providers;

use App\Services\PayChangu\FakePayChanguClient;
use App\Services\PayChangu\PayChanguClient;
use App\Services\PayChangu\PayChanguGateway;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The Windows `php artisan serve` runs single-process and can't inherit -d flags.
        // Filament pages pull enough concurrent assets that the default 30s max_execution_time
        // trips during dev. Lift the ceiling in local only; production runs on php-fpm which
        // is multi-process and doesn't need this.
        if (app()->environment('local') && function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $this->app->singleton(PayChanguGateway::class, function ($app) {
            $config = $app['config']->get('services.paychangu');
            $webhookSecret = (string) ($config['webhook_secret'] ?? '');

            if (!empty($config['fake'])) {
                return new FakePayChanguClient($webhookSecret);
            }

            return new PayChanguClient(
                baseUrl: rtrim((string) ($config['base_url'] ?? 'https://api.paychangu.com'), '/'),
                secretKey: (string) ($config['secret_key'] ?? ''),
                webhookSecret: $webhookSecret,
            );
        });
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
