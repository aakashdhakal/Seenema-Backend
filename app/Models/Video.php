<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'resolutions',
        'thumbnail_path',
        'backdrop_path',
        'duration',
        'user_id',
        'category',
        'rating',
        'status',
        'content_rating'
    ];

    protected $casts = [
        'resolutions' => 'array',
    ];

    const STATUS_PROCESSING = 'processing';
    const STATUS_READY = 'ready';
    const STATUS_FAILED = 'failed';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}