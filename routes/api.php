<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Load role-based route files
require __DIR__.'/api/user.php';
require __DIR__.'/api/resort_owner.php';
require __DIR__.'/api/admin.php';
