<?php
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register', 'refresh', 'user'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000'], // NOT '*', only allow frontend

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // 🔥 VERY IMPORTANT 🔥

];