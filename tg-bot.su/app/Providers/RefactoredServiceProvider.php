<?php

namespace App\Providers;

use App\Bridges\Abstracts\GoogleSheetsBridgeInterface;
use App\Bridges\GoogleSheetsBridge;
use App\Repositories\Bot\Abstracts\BotSettingsRepositoryInterface;
use App\Repositories\Bot\BotSettingsRepository;
use App\Repositories\Telegram\Abstracts\TelegraphBotRepositoryInterface;
use App\Repositories\Telegram\Abstracts\TelegraphChatRepositoryInterface;
use App\Repositories\Telegram\Abstracts\TelegraphMessageRepositoryInterface;
use App\Repositories\Telegram\TelegraphBotRepository;
use App\Repositories\Telegram\TelegraphChatRepository;
use App\Repositories\Telegram\TelegraphMessageRepository;
use App\Services\Bot\Abstracts\BotSettingsServiceInterface;
use App\Services\Bot\BotSettingsService;
use App\Services\Chat\Abstracts\ChatNameFormatterServiceInterface;
use App\Services\Chat\ChatNameFormatterService;
use App\Services\Report\Abstracts\HashtagReportServiceInterface;
use App\Services\Report\Abstracts\PeriodCalculationServiceInterface;
use App\Services\Report\HashtagReportService;
use App\Services\Report\PeriodCalculationService;
use App\Services\Telegram\Abstracts\AuthorizationServiceInterface;
use App\Services\Telegram\Abstracts\ChatManagementServiceInterface;
use App\Services\Telegram\Abstracts\HashtagCommandServiceInterface;
use App\Services\Telegram\Abstracts\MenuServiceInterface;
use App\Services\Telegram\Abstracts\ReportCommandServiceInterface;
use App\Services\Telegram\Abstracts\SettingsCommandServiceInterface;
use App\Services\Telegram\AuthorizationService;
use App\Services\Telegram\ChatManagementService;
use App\Services\Telegram\HashtagCommandService;
use App\Services\Telegram\MenuService;
use App\Services\Telegram\ReportCommandService;
use App\Services\Telegram\SettingsCommandService;
use Illuminate\Support\ServiceProvider;

class RefactoredServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(BotSettingsRepositoryInterface::class, BotSettingsRepository::class);
        $this->app->singleton(TelegraphMessageRepositoryInterface::class, TelegraphMessageRepository::class);
        $this->app->singleton(TelegraphChatRepositoryInterface::class, TelegraphChatRepository::class);
        $this->app->singleton(TelegraphBotRepositoryInterface::class, TelegraphBotRepository::class);

        $this->app->singleton(GoogleSheetsBridgeInterface::class, GoogleSheetsBridge::class);

        $this->app->singleton(PeriodCalculationServiceInterface::class, PeriodCalculationService::class);
        $this->app->singleton(BotSettingsServiceInterface::class, BotSettingsService::class);
        $this->app->singleton(MenuServiceInterface::class, MenuService::class);
        $this->app->singleton(AuthorizationServiceInterface::class, AuthorizationService::class);
        $this->app->singleton(SettingsCommandServiceInterface::class, SettingsCommandService::class);
        $this->app->singleton(HashtagCommandServiceInterface::class, HashtagCommandService::class);
        $this->app->singleton(ReportCommandServiceInterface::class, ReportCommandService::class);
        $this->app->singleton(ChatManagementServiceInterface::class, ChatManagementService::class);
        $this->app->singleton(ChatNameFormatterServiceInterface::class, ChatNameFormatterService::class);

        $this->app->singleton(HashtagReportServiceInterface::class, HashtagReportService::class);
    }

    public function boot()
    {
        //
    }
}
