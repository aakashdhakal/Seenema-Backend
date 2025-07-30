<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WatchList;
use Illuminate\Http\Request;

class WatchListController extends Controller
{
    public function addToWatchList(Request $request)
    {
        $request->validate(['video_id' => 'required|exists:videos,id']);
        WatchList::firstOrCreate([
            'user_id' => auth()->id(),
            'video_id' => $request->video_id,
        ]);
        return response()->json(['message' => 'Added to watchlist', 'success' => true]);
    }

    public function removeFromWatchList($id)
    {
        WatchList::where('user_id', auth()->id())
            ->where('video_id', $id)
            ->delete();
        return response()->json(['message' => 'Removed from watchlist', 'success' => true]);
    }

    public function getWatchList()
    {
        $videos = WatchList::where('user_id', auth()->id())->with('video')->get();
        return response()->json($videos);
    }

    public function checkIfVideoInWatchList($id)
    {
        $exists = WatchList::where('user_id', auth()->id())
            ->where('video_id', $id)
            ->exists();
        return response()->json(['exists' => $exists]);
    }
}
