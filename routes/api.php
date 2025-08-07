<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::apiResource('etudiants', App\Http\Controllers\Api\EtudiantController::class);
Route::apiResource('livres', App\Http\Controllers\Api\LivreController::class);
Route::apiResource('emprunts', App\Http\Controllers\Api\EmpruntController::class);
