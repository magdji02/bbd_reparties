<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('etudiants.index');
});

Route::get('/api/view/etudiants', function () {
    return view('etudiants.index');
})->name('etudiants.index');

Route::get('/api/view/livres', function () {
    return view('livres.index');
})->name('livres.index');

Route::get('/api/view/emprunts', function () {
    return view('emprunts.index');
})->name('emprunts.index');