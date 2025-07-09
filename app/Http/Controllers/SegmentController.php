<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;


class SegmentController extends Controller
{
    public function getInit($videoId, $resolution)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video) {
            $initSegmentPath = storage_path("app/public/videos/$videoId/$resolution/init.mp4");
            if (Storage::disk('public')->exists("videos/$videoId/$resolution/init.mp4")) {
                return response()->file($initSegmentPath);
            } else {
                return response()->json(['message' => 'Init segment not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Video not found'], 404);
        }
    }

    public function getSegment($videoId, $resolution, $segment)
    {
        $video = Video::where('id', $videoId)->first();

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        $relativePath = "videos/$videoId/$resolution/$segment";
        $absolutePath = storage_path("app/public/$relativePath");

        if (!Storage::disk('public')->exists($relativePath)) {
            return response()->json(['message' => 'Segment not found'], 404);
        }

        // 💡 Get size to prevent Transfer-Encoding: chunked
        $fileSize = filesize($absolutePath);

        return response()->stream(function () use ($absolutePath) {
            readfile($absolutePath);
        }, 200, [
            'Content-Type' => 'video/mp4',
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=31536000',
            'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
        ]);
    }

    public function getManifest($videoId)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video && Storage::disk('public')->exists("videos/$videoId/manifest.m3u8")) {
            return response()->file(storage_path("app/public/videos/$videoId/manifest.m3u8"));
        } else {
            return response()->json(['message' => 'Manifest not found'], 404);
        }
    }
    public function getManifestByResolution($videoId, $resolution)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video) {
            $manifestPath = storage_path("app/public/videos/$videoId/$resolution/playlist.m3u8");
            if (Storage::disk('public')->exists("videos/$videoId/$resolution/playlist.m3u8")) {
                return response()->file($manifestPath);
            } else {
                return response()->json(['message' => 'Manifest for the specified resolution not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Video not found'], 404);
        }
    }

    public function getSegmentSizes($segment, $videoId)
    {
        $video = Video::where('id', $videoId)->first();
        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        // Define available resolutions (should match your encoding profiles)
        //g get resolution from the folders
        $resolutions = ['144p', '240p', '480p', '720p', '1080p', '2160p'];
        $sizes = [];

        foreach ($resolutions as $resolution) {
            $segmentPath = storage_path("app/public/videos/$videoId/$resolution/$segment");
            if (Storage::disk('public')->exists("videos/$videoId/$resolution/$segment")) {
                $sizes[$resolution] = filesize($segmentPath);
            } else {
                $sizes[$resolution] = null; // or you can skip this resolution
            }
        }

        return response()->json(['sizes' => $sizes], 200);
    }

    public function getIntroVideo($resolution)
    {
        $introPath = storage_path("app/public/videos/intro/$resolution/segment_000.m4s");
        if (Storage::disk('public')->exists("videos/intro/$resolution/segment_000.m4s")) {
            return response()->file($introPath);
        } else {
            return response()->json(['message' => 'Intro video not found'], 404);
        }
    }

    public function getIntroInit($resolution)
    {
        $introPath = storage_path("app/public/videos/intro/$resolution/init.mp4");
        if (Storage::disk('public')->exists("videos/intro/$resolution/init.mp4")) {
            return response()->file($introPath);
        } else {
            return response()->json(['message' => 'Intro init segment not found'], 404);
        }
    }

    public function getIntroManifest($resolution)
    {
        $introManifestPath = storage_path("app/public/videos/intro/$resolution/playlist.m3u8");
        if (Storage::disk('public')->exists("videos/intro/$resolution/playlist.m3u8")) {
            return response()->file($introManifestPath);
        } else {
            return response()->json(['message' => 'Intro manifest not found'], 404);
        }
    }
}
