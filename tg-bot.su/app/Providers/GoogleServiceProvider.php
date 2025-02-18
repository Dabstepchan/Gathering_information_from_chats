<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Revolution\Google\Sheets\Sheets;
use Google_Client;
use Google_Service_Sheets;

class GoogleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Sheets::class, function ($app) {
            $client = new Google_Client();
            
            $client->setApplicationName(config('google.application_name'));
            $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
            $client->setAccessType('offline');
            
            $credentialsPath = storage_path('app/google/credentials.json');
            
            if (!file_exists($credentialsPath)) {
                throw new \Exception("Google credentials file not found at {$credentialsPath}");
            }
            
            $client->setAuthConfig($credentialsPath);
            
            $service = new Google_Service_Sheets($client);
            
            return new Sheets($service);
        });
    }

    public function boot()
    {
        //
    }
}