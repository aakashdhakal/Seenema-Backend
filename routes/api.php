<?php

use App\Http\Controllers\SegmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WatchHistoryController;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;


Route::get('/ping', function () {
    return response()->json([
        'message' => 'Pong from Laravel!'
    ]);
});


// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/user', [AuthController::class, 'getAuthenticatedUser'])->middleware('auth:sanctum');


//email verification routes
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])->middleware(['throttle:6,1'])->name('verification.send');


// oauth routes
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/callback', [AuthController::class, 'handleGoogleCallback']);

// video routes
Route::post('/createVideoEntry', [VideoController::class, 'createVideoEntry']);
Route::post('/uploadVideoChunk', [VideoController::class, 'uploadChunk']);

Route::get('/getVideo/{id}', [VideoController::class, 'getVideo']);
Route::get('/getVideoStatus/{videoId}', [VideoController::class, 'getVideoStatus']);
Route::delete('/deleteVideo/{videoId}', [VideoController::class, 'deleteVideo']);

// Video display for users routes
Route::get('/getFeaturedVideo', [VideoController::class, 'getFeaturedVideo']);
Route::get('/getTrendingVideos', [VideoController::class, 'getTrendingVideos']);
Route::get('/getPopularVideos', [VideoController::class, 'getPopularVideos']);
Route::get('/getNewReleases', [VideoController::class, 'getNewReleases']);
Route::get('/getVideosByCategory/{category}', [VideoController::class, 'getVideoByCategory']);
Route::get('/getContinueWatching', [VideoController::class, 'getContinueWatching']);
Route::get('/getVideoById/{videoId}', [VideoController::class, 'getVideoById']);

//segment routes
Route::get('/video/', [SegmentController::class, 'getVideoSegments']);
Route::get('/init/{id}/{resolution}', [SegmentController::class, 'getInit']);
Route::get('/segment/{videoId}/{resolution}/{segment}', [SegmentController::class, 'getSegment']);
Route::get('/manifest/{videoId}', [SegmentController::class, 'getManifest']);
Route::get('/manifest/{videoId}/{resolution}', [SegmentController::class, 'getManifestByResolution']);
Route::get('/getSegmentSizes/{videoId}/{segment}', [SegmentController::class, 'getSegmentSizes']);
Route::get('/getIntroVideo/{resolution}', [SegmentController::class, 'getIntroVideo']);
Route::get('/getIntroInit/{resolution}', [SegmentController::class, 'getIntroInit']);
Route::get('/getIntroManifest/{resolution}', [SegmentController::class, 'getIntroManifest']);


// Video Recommendation Routes
Route::get('/recommendations/{videoId}', [VideoController::class, 'getRecommendations']);
Route::get('/getAllVideos', [VideoController::class, 'getAllVideos']);

// Watch History Routes (Protected)
Route::get('/history', [WatchHistoryController::class, 'getHistory']);
Route::get('/history/resume/{videoId}', [WatchHistoryController::class, 'getResumePoint']);
Route::post('/updateWatchHistory', [WatchHistoryController::class, 'updateProgress']);
Route::delete('/history/remove/{videoId}', [WatchHistoryController::class, 'removeHistoryItem']);
Route::delete('/history/clear', [WatchHistoryController::class, 'clearHistory']);

//Person credits route
Route::get('/getPeople', [PersonController::class, 'getPeople']);
Route::post('/addPerson', [PersonController::class, 'createPerson']);
Route::post('/addCreditsToVideo', [PersonController::class, 'addPersonToVideo']);

//Genre Routes
Route::get('/getGenres', [GenreController::class, 'getAllGenres']);
Route::post('/addGenreToVideo', [GenreController::class, 'addGenreToVideo']);

// Tag Routes
Route::post('/addTagsToVideo', [TagController::class, 'addTagsToVideo']);
// Route::get('/getTags', [TagController::class, 'getAllTags']);

//Users Route
Route::get('/getUsers', [UserController::class, 'getAllUsers']);


