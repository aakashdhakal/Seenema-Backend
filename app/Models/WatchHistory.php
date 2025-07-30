<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class WatchHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'watch_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'video_id',
        'watched_duration',
        'finished_at',
        'continue_watching', // add this

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'watched_duration' => 'float',
        'finished_at' => 'datetime',
        'continue_watching' => 'boolean', // add this

    ];

    /**
     * Get the user that owns the watch history record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the video that is part of the watch history record.
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}