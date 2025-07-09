<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


class GenreController extends Controller
{
    public function getAllGenres()
    {
        $genres = Genre::withCount('videos')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'color', 'is_active', 'created_at']);

        return response()->json($genres);
    }

    public function addGenreToVideo(Request $request)
    {
        // 1. Add validation. This is the most important fix.
        // It ensures 'video_id' exists in the 'videos' table before proceeding.
        $validated = $request->validate([
            'videoId' => 'required|integer|exists:videos,id',
            'genres' => 'required|array|min:1',
            'genres.*' => 'required|string|max:255', // Ensures each item in the array is a string
        ]);

        // 2. Find the video ONCE, before the loop.
        // We use the validated data, which we now know is safe.
        $video = Video::findOrFail($validated['videoId']);

        $genreIds = [];
        // 3. Loop through the genre names to find or create them and collect their IDs.
        foreach ($validated['genres'] as $genreName) {
            $genre = Genre::firstOrCreate(
                ['name' => trim($genreName)],
                ['slug' => Str::slug(trim($genreName))]
            );
            $genreIds[] = $genre->id;
        }

        // 4. Attach all the genres at once outside the loop.
        // Using syncWithoutDetaching is safe and efficient. It adds the new genres
        // without removing any that might already be attached to the video.
        $video->genres()->syncWithoutDetaching($genreIds);

        return response()->json(['message' => 'Genres added to video successfully'], 200);
    }


}