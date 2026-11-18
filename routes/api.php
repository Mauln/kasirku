<?php

use Illuminate\Support\Facades\Route;

// API routes akan ditambahkan bertahap mulai Fase 1
Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'version' => config('app.version'),
    'environment' => config('app.deploy_env'),
]));
