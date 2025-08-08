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
use App\Http\Controllers\WatchListController;
use App\Http\Controllers\FavouritesController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Storage;


Route::get('/ping', function () {
    return response()->json([
        'message' => 'Pong from Laravel!'
    ]);
});

Route::get('/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'getAuthenticatedUser'])->middleware('auth:sanctum');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');
    Route::post('/email/notify', [AuthController::class, 'sendVerificationEmail'])->middleware(['throttle:6,1'])->name('verification.send');
    Route::get('/google/redirect', function () {
        return Socialite::driver('google')->redirect();
    });
    Route::get('/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});


// Video routes
Route::prefix('video')->group(function () {
    Route::post('/create', [VideoController::class, 'createVideoEntry']);
    Route::post('/chunk/upload', [VideoController::class, 'uploadChunk']);
    Route::get('/status/{videoId}', [VideoController::class, 'getVideoStatus']);
    Route::delete('/{videoId}', [VideoController::class, 'deleteVideo']);
    // Route::get('/featured', [VideoController::class, 'getFeaturedVideo']);
    // Route::get('/trending', [VideoController::class, 'getTrendingVideos']);
    // Route::get('/popular', [VideoController::class, 'getPopularVideos']);
    // Route::get('/new-release', [VideoController::class, 'getNewReleases']);
    // Route::get('/category/{category}', [VideoController::class, 'getVideoByCategory']);
    // Route::get('/continue-watching', [VideoController::class, 'getContinueWatching']);
    // Route::get('/recommendations', [VideoController::class, 'getRecommendations']);
    Route::get('/home', [VideoController::class, 'getHomePageData']);
    Route::get('/all/paginate', [VideoController::class, 'getAllPaginatedVideos']);
    Route::get('/all', [VideoController::class, 'getAllVideos']);
    //serve subtitles
    Route::get('/subtitles/{videoId}', [VideoController::class, 'getSubtitles']);
    Route::post('/update/{videoId}', [VideoController::class, 'updateVideoDetails']);
    Route::get('/{slugOrId}', [VideoController::class, 'getVideoBySlugOrId']);
});

//Stream Segment Routes
Route::prefix('stream')->group(function () {
    Route::get('/segment/all', [SegmentController::class, 'getVideoSegments']);
    Route::get('/init/{id}/{resolution}', [SegmentController::class, 'getInit']);
    Route::get('/segment/{videoId}/{resolution}/{segment}', [SegmentController::class, 'getSegment']);
    Route::get('/manifest/{videoId}', [SegmentController::class, 'getManifest']);
    Route::get('/manifest/{videoId}/{resolution}', [SegmentController::class, 'getManifestByResolution']);
    Route::get('/segment-size/{videoId}/{segment}', [SegmentController::class, 'getSegmentSizes']);
    Route::get('/intro/{resolution}', [SegmentController::class, 'getIntroVideo']);
    Route::get('/intro/init/{resolution}', [SegmentController::class, 'getIntroInit']);
    Route::get('/intro/manifest/{resolution}', [SegmentController::class, 'getIntroManifest']);
});


// Watch History Routes 
Route::prefix('history')->group(function () {
    Route::get('/', [WatchHistoryController::class, 'getHistory']);
    Route::get('/resume/{videoId}', [WatchHistoryController::class, 'getResumePoint']);
    Route::post('/update', [WatchHistoryController::class, 'updateProgress']);
    Route::delete('/{videoId}', [WatchHistoryController::class, 'removeHistoryItem']);
    Route::delete('/clear', [WatchHistoryController::class, 'clearHistory']);
    Route::delete('/continue-watching/{videoId}', [WatchHistoryController::class, 'removeFormContinueWatching']);
    Route::get('/stats', [WatchHistoryController::class, 'getUserStats']);
});

//Person credits route
Route::prefix('people')->group(function () {
    Route::get('/get', [PersonController::class, 'getPeople']);
    Route::post('/create', [PersonController::class, 'createPerson']);
    Route::post('/add-credit', [PersonController::class, 'addPersonToVideo']);
});

//Genre Routes
Route::prefix('genre')->group(function () {
    Route::get('/get', [GenreController::class, 'getAllGenres']);
    Route::post('/add', [GenreController::class, 'addGenreToVideo']);
});

// Tag Routes
Route::prefix('tags')->group(function () {
    Route::post('/add', [TagController::class, 'addTagsToVideo']);
});

Route::prefix('user')->group(function () {
    Route::post('/update', [UserController::class, 'updateProfile'])->middleware('auth:sanctum');
    Route::patch('/status/change', [UserController::class, 'changeUserStatus'])->middleware('auth:sanctum');
    Route::patch('/role/change', [UserController::class, 'changeUserRole'])->middleware('auth:sanctum');
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'getAllUsers']);
});

Route::prefix('watchlist')->group(function () {
    Route::post('/add', [WatchListController::class, 'addToWatchList']);
    Route::delete('/{id}', [WatchListController::class, 'removeFromWatchList']);
    Route::get('/', [WatchListController::class, 'getWatchList']);
    Route::get('/check/{id}', [WatchListController::class, 'checkIfVideoInWatchList']);
});

Route::prefix('favourites')->group(
    function () {
        Route::post('/add', [FavouritesController::class, 'addToFavourites']);
        Route::delete('/{id}', [FavouritesController::class, 'removeFromFavourites']);
        Route::get('/', [FavouritesController::class, 'getFavourites']);
    }
);
//Search Route
Route::get('/search', [VideoController::class, 'getSearchResults']);

//Notifications Route
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'getNotifications']);
    Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/delete/{id}', [NotificationController::class, 'deleteNotification']);
    Route::post('/delete-all', [NotificationController::class, 'deleteAllNotifications']);
    Route::post('/send', [NotificationController::class, 'sendNotification']);
});

