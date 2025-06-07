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
        'manifest_path',
        'bitrates',
        'segment_sizes',
        'thumbnail_path',
        'duration',
        'user_id',
        'category',
    ];

    protected $casts = [
        'bitrates' => 'array',
        'segment_sizes' => 'array',
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