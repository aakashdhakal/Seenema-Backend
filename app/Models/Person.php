<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'biography', 'profile_picture'];

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'credits')
            ->withPivot('credited_as')
            ->withTimestamps();
    }

    //  Credit to a person in a video


}
