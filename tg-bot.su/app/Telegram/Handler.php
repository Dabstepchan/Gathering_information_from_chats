<?php

namespace App\Telegram;

use RuntimeException;
use App\Services\Telegram\Abstracts\MenuServiceInterface;
use App\Services\Telegram\Abstracts\SettingsCommandServiceInterface;
use App\Services\Telegram\Abstracts\ReportCommandServiceInterface;
use App\Services\Telegram\Abstracts\HashtagCommandServiceInterface;
use App\Services\Telegram\Abstracts\AuthorizationServiceInterface;
use App\Services\Telegram\Abstracts\ChatManagementServiceInterface;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\DTO\User;
use Illuminate\Support\Stringable;

class Handler extends WebhookHandler
{
    public function __construct(
        private readonly MenuServiceInterface $menuService,
        private readonly SettingsCommandServiceInterface $settingsCommandService,
        private readonly ReportCommandServiceInterface $reportCommandService,
        private readonly HashtagCommandServiceInterface $hashtagCommandService,
        private readonly AuthorizationServiceInterface $authorizationService,
        private readonly ChatManagementServiceInterface $chatManagementService
    ) {
        parent::__construct();
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $this->hashtagCommandService->processChatMessage($text->toString(), $this->chat);
    }

    protected function handleChatMemberJoined(User $member): void
    {
        $this->chatManagementService->processBotJoined($this->chat, $member);

        if ($member->isBot()) {
            $this->sendWelcomeMessage();
        }
    }

    protected function handleChatMemberLeft(User $member): void
    {
        $this->chatManagementService->processBotLeft($this->chat, $member);
    }

    private function sendWelcomeMessage(): void
    {
        $this->chat->message(
            "Привет! 👋 Я бот для сбора отчетов. 🤖\n\n" .
            "Теперь я буду отслеживать сообщения с хештегами в этом чате. 📊\n\n" .
            "⚠️ Для корректной работы боту необходимы права администратора."
        )->send();
    }

    public function start(): void
    {
        if (!$this->authorizationService->isAdmin($this->getUser())) {
            $this->chat->message('У вас нет доступа к этому боту.')->send();
            return;
        }

        $this->menuService->showMainMenu($this->chat);
    }

    public function showMainMenu(): void
    {
        $this->guardAdmin();
        $this->menuService->showMainMenu($this->chat);
    }

    public function showMainMenu_copy(): void
    {
        $this->guardAdmin();
        $this->menuService->editToMainMenu($this->chat, $this->messageId);
    }

    public function settings(): void
    {
        $this->guardAdmin();
        $this->settingsCommandService->showSettings($this->chat, $this->messageId);
    }

    public function settings_reports(): void
    {
        $this->guardAdmin();
        $this->settingsCommandService->showReportSettings($this->chat, $this->messageId);
    }

    public function set_report_day(): void
    {
        $this->guardAdmin();
        $this->settingsCommandService->showDaySelector($this->chat, $this->messageId);
    }

    public function save_report_day(): void
    {
        $this->guardAdmin();
        $day = $this->data->get('day');
        $this->settingsCommandService->saveReportDay($day, $this->chat);
        $this->settings_reports();
    }

    public function set_report_time(): void
    {
        $this->guardAdmin();
        $this->settingsCommandService->showTimeSelector($this->chat, $this->messageId);
    }

    public function save_report_time(): void
    {
        $this->guardAdmin();
        $hour = $this->data->get('time');
        $this->settingsCommandService->saveReportTime($hour, $this->chat);
        $this->settings_reports();
    }

    public function set_period_weeks(): void
    {
        $this->guardAdmin();
        $this->settingsCommandService->showPeriodSelector($this->chat, $this->messageId);
    }

    public function save_period_weeks(): void
    {
        $this->guardAdmin();
        $weeks = $this->data->get('weeks');
        $this->settingsCommandService->savePeriodWeeks($weeks, $this->chat);
        $this->settings_reports();
    }

    public function manage_hashtags(): void
    {
        $this->guardAdmin();
        $this->hashtagCommandService->showHashtagManagement($this->chat, $this->messageId);
    }

    public function add_hashtag(): void
    {
        $this->guardAdmin();
        $this->hashtagCommandService->requestHashtagInput($this->chat);
    }

    public function remove_hashtag(): void
    {
        $this->guardAdmin();
        $tag = $this->data->get('tag');

        if ($tag) {
            $this->hashtagCommandService->removeHashtag($tag, $this->chat);
            $this->manage_hashtags();
        } else {
            $this->hashtagCommandService->showHashtagRemovalSelector($this->chat, $this->messageId);
        }
    }

    public function generateReport(): void
    {
        $this->guardAdmin();
        $this->reportCommandService->showReportMenu($this->chat, $this->messageId);
    }

    public function generate_report(): void
    {
        $this->guardAdmin();
        $this->reportCommandService->generateReport($this->chat);
        $this->showMainMenu();
    }

    public function info(): void
    {
        $this->guardAdmin();
        $this->chat->message("Бот для сбора отчетов из чатов.")->send();
    }

    private function guardAdmin(): void
    {
        if (!$this->authorizationService->isAdmin($this->getUser())) {
            throw new RuntimeException('Доступ запрещен');
        }
    }

    private function getUser(): ?User
    {
        return $this->callbackQuery?->from() ?? $this->message?->from();
    }
}
