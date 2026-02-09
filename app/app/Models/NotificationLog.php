<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $connection = 'mysql';
    protected $table = 'notification_logs';

    public $timestamps = false;

    protected $fillable = [
        'whatsapp_setting_id',
        'xui_client_id',
        'notification_type',
        'sent_date',
        'success',
    ];

    protected $casts = [
        'success' => 'boolean',
        'sent_date' => 'date',
    ];

    public function whatsappSetting()
    {
        return $this->belongsTo(WhatsappSetting::class);
    }
}
