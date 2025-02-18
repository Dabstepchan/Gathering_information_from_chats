<?php

return [
    'application_name' => env('GOOGLE_APPLICATION_NAME', ''),
    'client_id' => env('GOOGLE_CLIENT_ID', ''),
    'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
    'service_account_json' => storage_path(env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION', '')),
    'sheet_id' => env('GOOGLE_SHEET_ID', ''),
];