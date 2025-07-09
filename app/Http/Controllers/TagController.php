<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function createTag(array $tags)
    {
        foreach ($tags as $tagName) {
            // Check if the tag already exists
            $tag = Tag::where('name', $tagName)->first();
            if (!$tag) {
                // Create the tag if it doesn't exist
                Tag::create(['name' => $tagName]);
            }
        }
    }

    public function addTagsToVideo(Request $request)
    {
        // 1. Add validation to ensure the video_id is valid before proceeding.
        $validated = $request->validate([
            'videoId' => 'required|integer|exists:videos,id',
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|string|max:255',
        ]);

        // 2. Find the video ONCE using the validated and safe ID.
        $video = Video::findOrFail($validated['videoId']);

        $tagIds = [];
        // 3. Loop through the tag names to find or create them and collect their IDs.
        foreach ($validated['tags'] as $tagName) {
            // Use firstOrCreate for efficiency: finds the tag or creates it if it doesn't exist.
            $tag = Tag::firstOrCreate(
                ['name' => trim($tagName)],
                ['slug' => Str::slug(trim($tagName))]
            );
            $tagIds[] = $tag->id;
        }

        // 4. Attach all the tags at once outside the loop.
        // syncWithoutDetaching is efficient and prevents duplicate entries.
        $video->tags()->syncWithoutDetaching($tagIds);

        return response()->json(['message' => 'Tags added to video successfully'], 200);
    }

}