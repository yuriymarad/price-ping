<?php

namespace App\Providers;

use App\Contracts\MarketDataProvider;
use App\Contracts\NotificationSender;
use App\Events\AlertsTriggered;
use App\Events\PortfolioImported;
use App\Events\TickerPricesRefreshed;
use App\Integrations\MarketData\CachedMarketDataProvider;
use App\Integrations\MarketData\YahooMarketDataProvider;
use App\Integrations\Notifications\TelegramNotificationSender;
use App\Listeners\CheckAlertRulesAfterPricesRefreshed;
use App\Listeners\RefreshPricesAfterPortfolioImported;
use App\Listeners\SendTriggeredAlerts;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MarketDataProvider::class, function ($app) {
            return new CachedMarketDataProvider(
                $app->make(YahooMarketDataProvider::class)
            );
        });
        $this->app->bind(NotificationSender::class, TelegramNotificationSender::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Event::listen(PortfolioImported::class, RefreshPricesAfterPortfolioImported::class);
        Event::listen(TickerPricesRefreshed::class, CheckAlertRulesAfterPricesRefreshed::class);
        Event::listen(AlertsTriggered::class, SendTriggeredAlerts::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
