<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/db-test', function () {
    try {
        \DB::connection()->getPdo();
        return response()->json('Database connection is OK!');
    } catch (\Exception $e) {
        return response()->json('Database connection failed: ' . $e->getMessage(), 500);
    }
});

Route::get('/health', function () {
    return response()->json(['status' => 'OK'], 200);
});