<?php
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register', 'refresh', 'user', 'upload-video', 'video/*', 'storage/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000'], // Remove the /* from the end

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];