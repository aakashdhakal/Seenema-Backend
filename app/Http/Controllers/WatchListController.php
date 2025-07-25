<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
        return response()->json(['message' => 'Added to watchlist']);
    }

    public function removeFromWatchList(Request $request)
    {
        $request->validate(['video_id' => 'required|exists:videos,id']);
        WatchList::where('user_id', auth()->id())
            ->where('video_id', $request->video_id)
            ->delete();
        return response()->json(['message' => 'Removed from watchlist']);
    }

    public function getWatchList()
    {
        $videos = WatchList::where('user_id', auth()->id())->with('video')->get();
        return response()->json($videos);
    }
}
