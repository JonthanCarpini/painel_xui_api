<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\AppSetting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agendar rotação do cliente fantasma
Schedule::command('ghost:rotate')->dailyAt(AppSetting::get('ghost_rotation_time', '04:00'));

// Enviar notificações de vencimento via WhatsApp (3 dias, 1 dia, hoje)
Schedule::command('notifications:send-expiry')->everyFiveMinutes();
