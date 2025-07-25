<?php

namespace App\Bridges\Abstracts;

interface GoogleSheetsBridgeInterface
{
    public function initializeConnection(): void;

    public function createSheetWithData(string $spreadsheetId, string $sheetTitle, array $data): void;

    public function getSpreadsheetUrl(string $spreadsheetId, ?string $sheetTitle = null): string;
}
