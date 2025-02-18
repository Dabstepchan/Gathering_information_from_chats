<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\TelegraphMessage;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Revolution\Google\Sheets\Sheets;
use Google\Client;
use Google\Service\Sheets as GoogleSheets;
use App\Models\BotSettings;

class CheckHashtagReports extends Command
{
    protected $signature = 'reports:check {--start=} {--end=}';
    protected $description = 'Проверка хештегов в чатах';
    
    protected $sheets;

    public function __construct(Sheets $sheets)
    {
        parent::__construct();
        $this->sheets = $sheets;
    }

    //Инициализация Google Sheets
    protected function initializeGoogleSheets($sheetTitle, $sheetsData)
    {
        try {
            $credentialsPath = storage_path('app/google/credentials.json');
            if (!file_exists($credentialsPath)) {
                throw new \Exception("Credentials не найдены: " . $credentialsPath);
            }

            $spreadsheetId = env('GOOGLE_SHEET_ID');
            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->setScopes([GoogleSheets::SPREADSHEETS]);
            $client->setAccessType('offline');

            $service = new GoogleSheets($client);
            $this->sheets->setService($service);
            $spreadsheet = $this->sheets->spreadsheet($spreadsheetId);
            
            $existingSheets = $spreadsheet->sheetList();
            $sheetExists = in_array($sheetTitle, $existingSheets);
            
            if (!$sheetExists) {
                try {
                    $spreadsheet->addSheet($sheetTitle);
                    
                    $headerRow = ['Период', 'Хештег', 'Чат', 'Ссылка на чат'];
                    $spreadsheet->sheet($sheetTitle)->append([$headerRow]);
                } catch (\Exception $e) {
                    $existingSheets = $spreadsheet->sheetList();
                    if (!in_array($sheetTitle, $existingSheets)) {
                        throw $e;
                    }
                    $sheetExists = true;
                }
            }
            
            if (count($sheetsData) > 1) {
                $currentData = $spreadsheet->sheet($sheetTitle)->all();
                $existingData = array_slice($currentData, 1);
                
                $newData = array_slice($sheetsData, 1);
                
                $filteredData = array_filter($newData, function ($row) use ($existingData) {
                    return !in_array($row, $existingData);
                });
                
                if (!empty($filteredData)) {
                    $sheetsData = array_map(function ($row) {
                        return array_map('strval', $row);
                    }, $filteredData);
                    
                    $spreadsheet->sheet($sheetTitle)->append($sheetsData);
                }
            }

            return "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit#gid=0";
        } catch (\Exception $e) {
            $this->error("Ошибка при работе с Google Sheets: " . $e->getMessage());
            return "https://docs.google.com/spreadsheets/d/" . env('GOOGLE_SHEET_ID') . "/edit#gid=0";
        }
    }    

    protected function translateDayToEnglish($day)
    {
        $translations = [
            'Понедельник' => 'Monday',
            'Вторник' => 'Tuesday',
            'Среда' => 'Wednesday',
            'Четверг' => 'Thursday',
            'Пятница' => 'Friday',
            'Суббота' => 'Saturday',
            'Воскресенье' => 'Sunday',
        ];

        return $translations[$day] ?? $day;
    }

    public function handle()
    {
        $timezone = 'Asia/Novokuznetsk';
        $settings = BotSettings::first();

        //Определение периода для отчета
        if ($this->option('start') && $this->option('end')) {
            $startOfPeriod = Carbon::parse($this->option('start'), $timezone);
            $endOfPeriod = Carbon::parse($this->option('end'), $timezone);
        } else {
            $now = Carbon::now($timezone);
            $reportDay = $this->translateDayToEnglish($settings->report_day ?? 'Понедельник');
            $reportTime = $settings->report_time ?? '10:00:00';
            $periodWeeks = $settings->period_weeks ?? 1;
            
            $timeParts = explode(':', $reportTime);
            
            if ($now->format('H:i:s') < $reportTime && 
                $now->dayOfWeek === Carbon::parse($reportDay)->dayOfWeek) {
                $endOfPeriod = $now->copy()
                    ->subWeek()
                    ->next($reportDay)
                    ->setTime($timeParts[0], $timeParts[1], 0)
                    ->subSecond();
            } else {
                $endOfPeriod = $now->copy()
                    ->previous($reportDay)
                    ->setTime($timeParts[0], $timeParts[1], 0)
                    ->subSecond();
            }
            
            $startOfPeriod = $endOfPeriod->copy()
                ->subWeeks($periodWeeks - 1)
                ->addSecond();
        }

        $sheetTitle = $startOfPeriod->format('d.m.y H:i') . ' - ' . $endOfPeriod->format('d.m.y H:i');

        $this->info("Проверка сообщений за период:");
        $this->info("Начало периода: " . $startOfPeriod->toDateTimeString());
        $this->info("Конец периода: " . $endOfPeriod->toDateTimeString());

        $bot = TelegraphBot::first();
        if (!$bot) {
            $this->error("Бот не найден.");
            return;
        }

        //Получение списка чатов и сообщений
        $chats = TelegraphChat::where('telegraph_bot_id', $bot->id)->get();
        if ($chats->isEmpty()) {
            $this->error("Нет чатов с этим ботом.");
            return;
        }

        $chatIdsFromMessages = TelegraphMessage::distinct('telegraph_chat_id')
            ->pluck('telegraph_chat_id');

        $hashtags = $settings->hashtags ?? [
            '#митрепорт' => 'Тут не было митрепортов',
            '#еженедельныйотчет' => 'Тут не было еж.отчетов',
        ];

        $reports = [];
        $sheetsData = [['Период', 'Хештег', 'Чат', 'Ссылка на чат']];

        foreach ($hashtags as $hashtag => $reportTitle) {
            $missingChats = [];

            foreach ($chatIdsFromMessages as $chatId) {
                $chat = $chats->firstWhere('id', $chatId);

                if (!$chat) {
                    $this->error("Чат с ID {$chatId} не найден в TelegraphChat!");
                    continue;
                }

                $this->info("\nПроверка чата: {$chat->name} (ID: {$chatId}) для хештега {$hashtag}");

                $messages = TelegraphMessage::where('telegraph_chat_id', $chatId)
                    ->where('message', 'like', "%{$hashtag}%")
                    ->whereBetween('sent_at', [$startOfPeriod, $endOfPeriod])
                    ->get();

                $this->info("Найдено сообщений: " . $messages->count());

                if ($messages->isEmpty()) {
                    $chatName = $chat->name ?? "Неизвестный чат";
                
                    if ($chat->username) {
                        $rawChatLink = "https://t.me/{$chat->username}";
                    } elseif ($chat->chat_id < 0) {
                        $privateId = str_replace('-100', '', $chat->chat_id);
                        $rawChatLink = "https://t.me/c/{$privateId}";
                    } else {
                        $rawChatLink = "N/A";
                    }
                
                    $missingChats[] = "[{$chatName}]({$rawChatLink})";
                    $sheetsData[] = [$sheetTitle, $hashtag, $chatName, $rawChatLink];
                }                
            }

            if (!empty($missingChats)) {
                $reports[$hashtag] = "**{$reportTitle}**\n" . implode("\n", $missingChats);
            }
        }

        //Инициализация Google Sheets и запись данных
        $spreadsheetUrl = null;
        if (count($sheetsData) > 1) {
            $spreadsheetUrl = $this->initializeGoogleSheets($sheetTitle, $sheetsData);
        }

        //Отправка отчета в Telegram
        if (!empty($reports)) {
            $userId = env('ADMIN_USER_ID');
            
            foreach ($reports as $hashtag => $reportText) {
                try {
                    if ($spreadsheetUrl) {
                        $reportText .= "\n[Ссылка на таблицу с отчетом]({$spreadsheetUrl})";
                    }

                    Telegraph::bot($bot)
                        ->chat($userId)
                        ->markdown($reportText)
                        ->send();
                    
                    $this->info("Отчет по хештегу {$hashtag} отправлен");
                } catch (\Exception $e) {
                    $this->error("Ошибка при отправке отчета для {$hashtag}: " . $e->getMessage());
                }
            }
        } else {
            $this->info("Все чаты содержат нужные хештеги. Отчеты не отправлены.");
        }

        $this->info("Завершено.");
    }
}
