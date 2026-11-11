<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Dynamically overlay database-stored IVR and Twilio credentials into config
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('ivr_settings')) {
                $dbSid = \App\Models\IvrSetting::get('twilio_account_sid');
                $dbToken = \App\Models\IvrSetting::get('twilio_auth_token');
                $dbPhone = \App\Models\IvrSetting::get('twilio_phone_number');
                $dbWebhookVal = \App\Models\IvrSetting::get('twilio_webhook_validation');
                $dbAppUrl = \App\Models\IvrSetting::get('app_url');

                if (!empty($dbSid)) {
                    config(['twilio.account_sid' => $dbSid]);
                }
                if (!empty($dbToken)) {
                    config(['twilio.auth_token' => $dbToken]);
                }
                if (!empty($dbPhone)) {
                    config(['twilio.phone_number' => $dbPhone]);
                }
                if ($dbWebhookVal !== null) {
                    config(['twilio.webhook_validation' => filter_var($dbWebhookVal, FILTER_VALIDATE_BOOLEAN)]);
                }
                if (!empty($dbAppUrl)) {
                    config(['app.url' => $dbAppUrl]);
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore during migrations or CLI setup
        }
    }
}
