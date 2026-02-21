<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    // protected $connection = 'xui';
    protected $table = 'streams';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'category_id',
        'stream_display_name',
        'stream_source',
        'stream_icon',
        'notes',
        'enable_transcode',
        'transcode_attributes',
        'custom_ffmpeg',
        'movie_properties',
        'movie_subtitles',
        'read_native',
        'target_container',
        'stream_all',
        'remove_subtitles',
        'custom_sid',
        'epg_api',
        'epg_id',
        'channel_id',
        'epg_lang',
        'order',
        'auto_restart',
        'transcode_profile_id',
        'gen_timestamps',
        'added',
        'series_no',
        'direct_source',
        'tv_archive_duration',
        'tv_archive_server_id',
        'tv_archive_pid',
        'vframes_server_id',
        'vframes_pid',
        'movie_symlink',
        'rtmp_output',
        'allow_record',
        'probesize_ondemand',
        'custom_map',
        'external_push',
        'delay_minutes',
        'tmdb_language',
        'llod',
        'year',
        'rating',
        'plex_uuid',
        'uuid',
    ];

    protected $casts = [
        'type' => 'integer',
        'enable_transcode' => 'boolean',
        'read_native' => 'boolean',
        'stream_all' => 'boolean',
        'remove_subtitles' => 'boolean',
        'epg_api' => 'integer',
        'epg_id' => 'integer',
        'order' => 'integer',
        'transcode_profile_id' => 'integer',
        'gen_timestamps' => 'boolean',
        'added' => 'integer',
        'series_no' => 'integer',
        'direct_source' => 'boolean',
        'tv_archive_duration' => 'integer',
        'tv_archive_server_id' => 'integer',
        'tv_archive_pid' => 'integer',
        'vframes_server_id' => 'integer',
        'vframes_pid' => 'integer',
        'movie_symlink' => 'boolean',
        'rtmp_output' => 'boolean',
        'allow_record' => 'boolean',
        'probesize_ondemand' => 'integer',
        'delay_minutes' => 'integer',
        'llod' => 'boolean',
        'year' => 'integer',
        'rating' => 'float',
    ];
}
