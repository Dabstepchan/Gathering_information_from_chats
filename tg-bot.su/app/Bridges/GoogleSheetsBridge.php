<?php

namespace App\Bridges;

use Google\Client;
use Google\Service\Sheets as GoogleSheets;
use Revolution\Google\Sheets\Sheets;
use Exception;
use App\Bridges\Abstracts\GoogleSheetsBridgeInterface;

class GoogleSheetsBridge implements GoogleSheetsBridgeInterface
{
    protected Sheets $sheets;
    protected Client $client;
    protected GoogleSheets $service;

    /**
     * @throws Exception
     */
    public function __construct(Sheets $sheets)
    {
        $this->sheets = $sheets;
        $this->initializeConnection();
    }

    /**
     * @throws Exception
     */
    public function initializeConnection(): void
    {
        try {
            $credentials = [
                'type' => 'service_account',
                'project_id' => config('google.project_id'),
                'private_key_id' => config('google.private_key_id'),
                'private_key' => config('google.private_key'),
                'client_email' => config('google.client_email'),
                'client_id' => config('google.client_id'),
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/' . urlencode(config('google.client_email')),
                'universe_domain' => 'googleapis.com'
            ];

            $this->client = new Client();
            $this->client->setAuthConfig($credentials);
            $this->client->setScopes(config('google.scopes'));
            $this->client->setAccessType('offline');
            $this->client->setApplicationName(config('google.application_name'));

            $this->service = new GoogleSheets($this->client);
            $this->sheets->setService($this->service);
        } catch (Exception $e) {
            throw new Exception("Ошибка инициализации Google Sheets: " . $e->getMessage());
        }
    }

    private function getSpreadsheet(string $spreadsheetId): Sheets
    {
        return $this->sheets->spreadsheet($spreadsheetId);
    }

    /**
     * @throws Exception
     */
    public function createSheetWithData(string $spreadsheetId, string $sheetTitle, array $data): void
    {
        try {
            $spreadsheet = $this->getSpreadsheet($spreadsheetId);
            $existingSheets = $spreadsheet->sheetList();
            $sheetExists = in_array($sheetTitle, $existingSheets);

            if (!$sheetExists) {
                $spreadsheet->addSheet($sheetTitle);
                $headerRow = ['Период', 'Хештег', 'Чат', 'Ссылка на чат'];
                $spreadsheet->sheet($sheetTitle)->append([$headerRow]);
            }

            if (count($data) > 1) {
                $currentData = $spreadsheet->sheet($sheetTitle)->all();
                $existingData = array_slice($currentData, 1);
                $newData = array_slice($data, 1);

                $filteredData = array_filter($newData, function ($row) use ($existingData) {
                    return !in_array($row, $existingData);
                });

                if (!empty($filteredData)) {
                    $processedData = array_map(function ($row) {
                        return array_map('strval', $row);
                    }, $filteredData);

                    $spreadsheet->sheet($sheetTitle)->append($processedData);
                }
            }
        } catch (Exception $e) {
            throw new Exception("Ошибка при создании листа: " . $e->getMessage());
        }
    }

    public function getSpreadsheetUrl(string $spreadsheetId, ?string $sheetTitle = null): string
    {
        if ($sheetTitle) {
            $gid = $this->getSheetGid($spreadsheetId, $sheetTitle);
            return "https://docs.google.com/spreadsheets/d/$spreadsheetId/edit#gid=$gid";
        }

        return "https://docs.google.com/spreadsheets/d/$spreadsheetId/edit#gid=0";
    }

    /**
     * Получает gid (идентификатор) листа по его названию
     */
    private function getSheetGid(string $spreadsheetId, string $sheetTitle): int
    {
        try {
            $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheet->getSheets();

            foreach ($sheets as $sheet) {
                if ($sheet->getProperties()->getTitle() === $sheetTitle) {
                    return $sheet->getProperties()->getSheetId();
                }
            }

            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
