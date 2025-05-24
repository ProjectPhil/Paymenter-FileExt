<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadFile extends Model
{
    protected $fillable = [
        'service_id',
        'filename',
        'original_name',
        'file_size',
        'download_count',
        'expires_at',
        'max_downloads'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'download_count' => 'integer',
        'max_downloads' => 'integer',
        'file_size' => 'integer'
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
} 