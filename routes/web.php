<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'E-commerce Order Management System API',
        'version' => '1.0',
        'docs' => 'Check API documentation for available endpoints'
    ]);
});
