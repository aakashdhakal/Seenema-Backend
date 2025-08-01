<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'ffmpeg' => [
        'path' => env('FFMPEG_PATH', 'ffmpeg'),
        'ffprobe_path' => env('FFPROBE_PATH', 'ffprobe'),
        'profiles' => [
            ['label' => '144p', 'resolution' => '256x144', 'bitrate' => '150k', 'height' => 144],
            ['label' => '240p', 'resolution' => '426x240', 'bitrate' => '400k', 'height' => 240],
            ['label' => '480p', 'resolution' => '854x480', 'bitrate' => '1000k', 'height' => 480],
            ['label' => '720p', 'resolution' => '1280x720', 'bitrate' => '2500k', 'height' => 720],
            ['label' => '1080p', 'resolution' => '1920x1080', 'bitrate' => '5000k', 'height' => 1080],
            ['label' => '2160p', 'resolution' => '3840x2160', 'bitrate' => '10000k', 'height' => 2160],
        ],
    ],

];
