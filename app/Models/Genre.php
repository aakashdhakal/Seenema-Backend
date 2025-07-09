<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];


    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($genre) {
            if (empty($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
            }
        });

        static::updating(function ($genre) {
            if ($genre->isDirty('name') && empty($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
            }
        });
    }

    /**
     * Relationship with videos
     */
    public function videos()
    {
        return $this->belongsToMany(Video::class, 'video_genres');
    }

}