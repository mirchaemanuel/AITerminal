<?php

use Illuminate\Support\Facades\File;

return [
    'paths' => [
        resource_path('views'),
    ],

    'compiled' => \Phar::running()
        ? $_SERVER['HOME'].'/.ai_terminal/cache/views'
        : env('VIEW_COMPILED_PATH', realpath(storage_path('framework/views'))),
];
