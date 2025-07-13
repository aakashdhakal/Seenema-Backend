<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Str;


class PersonController extends Controller
{
    public function getPeople()
    {
        return Person::orderBy('name')->get();
    }

    public function createPerson(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:people,name',
            'biography' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);
        $appUrl = env('APP_URL');

        $person = new Person($request->only('name', 'biography'));

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->storeAs('images/person', Str::slug($request->name) . '.' . $request->file('profile_picture')->getClientOriginalExtension(), 'public');
            $person->profile_picture = "$appUrl/storage/$path";
        }

        $person->save();

        return response()->json($person, 201);
    }

    /**
     * Attach one or more people to a video with their specific roles and credits.
     */
    public function addPersonToVideo(Request $request)
    {
        // 1. Updated validation to include the 'role' and make 'credited_as' nullable.
        $validated = $request->validate([
            'videoId' => 'required|integer|exists:videos,id',
            'credits' => 'required|array|min:1',
            'credits.*.person_id' => 'required|integer|exists:people,id',
            'credits.*.credited_as' => 'nullable|string|max:255',
        ]);

        // 2. Find the video using the validated ID.
        $video = Video::findOrFail($validated['videoId']);

        $peopleToSync = [];
        // 3. Prepare the data for syncing, including all pivot data.
        foreach ($validated['credits'] as $personCredit) {
            $peopleToSync[$personCredit['person_id']] = [
                'credited_as' => $personCredit['credited_as']
            ];
        }

        // 4. Use syncWithoutDetaching for a safe and efficient update.
        // This adds the new credits without removing existing ones and prevents errors on duplicates.
        $video->people()->syncWithoutDetaching($peopleToSync);

        return response()->json(['message' => 'People added to video successfully'], 200);
    }


}