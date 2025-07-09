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
        'rating',
        'status',
        'content_rating',
        'language',
        'visibility',
        'release_date',
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
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_video');
    }
    public function people()
    {
        return $this->belongsToMany(Person::class, 'credits')
            ->withPivot('credited_as')
            ->withTimestamps();
    }
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'video_genres');
    }

}