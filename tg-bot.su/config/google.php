<?php

return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_email' => env('GOOGLE_CLIENT_EMAIL'),
    'application_name' => env('GOOGLE_APPLICATION_NAME', 'Laravel Google Sheets'),
    'project_id' => env('GOOGLE_PROJECT_ID'),
    'private_key_id' => env('GOOGLE_PRIVATE_KEY_ID'),
    'private_key' => env('GOOGLE_PRIVATE_KEY'),
    'spreadsheet_id' => env('GOOGLE_SHEET_ID'),
    'scopes' => [
        'https://www.googleapis.com/auth/spreadsheets',
        'https://www.googleapis.com/auth/drive',
    ],
];