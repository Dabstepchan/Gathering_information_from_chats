<?php

namespace App\Services\Report;

use App\Bridges\Abstracts\GoogleSheetsBridgeInterface;
use App\DTO\ChatReportDTO;
use App\DTO\HashtagReportDTO;
use App\Repositories\Bot\Abstracts\BotSettingsRepositoryInterface;
use App\Repositories\Telegram\Abstracts\TelegraphBotRepositoryInterface;
use App\Repositories\Telegram\Abstracts\TelegraphChatRepositoryInterface;
use App\Repositories\Telegram\Abstracts\TelegraphMessageRepositoryInterface;
use App\Services\Report\Abstracts\HashtagReportServiceInterface;
use App\Services\Report\Abstracts\PeriodCalculationServiceInterface;
use App\Services\Bot\Abstracts\BotSettingsServiceInterface;
use DefStudio\Telegraph\Facades\Telegraph;
use Exception;
use Illuminate\Support\Carbon;

class HashtagReportService implements HashtagReportServiceInterface
{
    public function __construct(
        protected BotSettingsRepositoryInterface $botSettingsRepository,
        protected TelegraphMessageRepositoryInterface $messageRepository,
        protected TelegraphChatRepositoryInterface $chatRepository,
        protected TelegraphBotRepositoryInterface $botRepository,
        protected GoogleSheetsBridgeInterface $googleSheetsBridge,
        protected PeriodCalculationServiceInterface $periodCalculationService,
        protected BotSettingsServiceInterface $botSettingsService
    ) {}

    /**
     * @throws Exception
     */
    public function generateReport(?Carbon $startDate = null, ?Carbon $endDate = null): void
    {
        $settings = $this->botSettingsRepository->first();
        if (!$settings) {
            throw new Exception('Настройки бота не найдены');
        }

        $settingsDTO = $this->botSettingsService->getBotSettingsAsDTO();
        $period = $this->periodCalculationService->calculateReportPeriod($settingsDTO, $startDate, $endDate);
        
        $startOfPeriod = $period->startDate;
        $endOfPeriod = $period->endDate;
        $sheetTitle = $startOfPeriod->format('d.m.y H:i') . ' - ' . $endOfPeriod->format('d.m.y H:i');

        $bot = $this->botRepository->getFirstBot();
        if (!$bot) {
            throw new Exception('Бот не найден');
        }

        $chats = $this->chatRepository->getChatsByBotId($bot->id);
        if ($chats->isEmpty()) {
            throw new Exception('Нет чатов с этим ботом');
        }

        $chatIdsFromMessages = $this->messageRepository->getDistinctGroupChatIds();
        $hashtags = $this->botSettingsRepository->getHashtags();

        $hashtagReports = [];
        $sheetsData = [['Период', 'Хештег', 'Чат', 'Ссылка на чат']];

        foreach ($hashtags as $hashtag => $reportTitle) {
            $missingChatLinks = [];

            foreach ($chatIdsFromMessages as $chatId) {
                $chat = $this->chatRepository->findChatById($chatId);
                if (!$chat) {
                    continue;
                }

                $messages = $this->messageRepository->getMessagesWithHashtag($chatId, $hashtag, $startOfPeriod, $endOfPeriod);

                if ($messages->isEmpty()) {
                    $chatName = $chat->name ?? "Неизвестный чат";
                    $rawChatLink = $this->generateChatLink($chat);

                    $chatReport = new ChatReportDTO($chatId, $chatName, $rawChatLink, [$hashtag]);
                    $missingChatLinks[] = $chatReport->getFormattedLink();

                    $sheetsData[] = [$sheetTitle, $hashtag, $chatName, $rawChatLink];
                }
            }

            if (!empty($missingChatLinks)) {
                $hashtagReport = new HashtagReportDTO($hashtag, $reportTitle, $missingChatLinks);
                $hashtagReports[] = $hashtagReport;
            }
        }

        $this->processReports($hashtagReports, $sheetsData, $sheetTitle, $bot);
    }

    protected function generateChatLink($chat): string
    {
        if ($chat->username) {
            return "https://t.me/$chat->username";
        } elseif (str_starts_with((string)$chat->chat_id, '-100')) {
            $privateId = substr((string)$chat->chat_id, 4);
            return "https://t.me/c/$privateId";
        } else {
            return "-";
        }
    }

    /**
     * @throws Exception
     */
    protected function processReports(array $hashtagReports, array $sheetsData, string $sheetTitle, $bot): void
    {
        $spreadsheetUrl = null;

        if (count($sheetsData) > 1) {
            $spreadsheetId = config('google.spreadsheet_id');
            $this->googleSheetsBridge->createSheetWithData($spreadsheetId, $sheetTitle, $sheetsData);
            $spreadsheetUrl = $this->googleSheetsBridge->getSpreadsheetUrl($spreadsheetId, $sheetTitle);
        }

        if (!empty($hashtagReports)) {
            $this->sendReportsToTelegram($hashtagReports, $spreadsheetUrl, $bot);
        }
    }

    /**
     * @throws Exception
     */
    protected function sendReportsToTelegram(array $hashtagReports, ?string $spreadsheetUrl, $bot): void
    {
        $userId = config('telegram.admin_user_id');

        foreach ($hashtagReports as $hashtagReport) {
            try {
                $reportText = $hashtagReport->getFormattedReport();

                if ($spreadsheetUrl) {
                    $reportText .= "\n[Ссылка на таблицу с отчетом]($spreadsheetUrl)";
                }

                Telegraph::bot($bot)
                    ->chat($userId)
                    ->markdown($reportText)
                    ->send();

            } catch (Exception $e) {
                throw new Exception("Ошибка при отправке отчета для $hashtagReport->hashtag: " . $e->getMessage());
            }
        }
    }
}