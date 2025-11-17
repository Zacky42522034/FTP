<?php

use App\Http\Controllers\EarthController;
use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\DocksController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\AllFileController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard')->middleware('auth');

Route::post('/upload', [FileController::class, 'upload'])->name('upload');
Route::get('/files', [FileController::class, 'getFiles']);
Route::get('/download/{filename}', [FileController::class, 'download'])->where('filename', '.*');
Route::get('/files/delete/{filename}', [FileController::class, 'delete'])->name('files.delete');

Route::post('/files/toggle-favorite', [FileController::class, 'toggleFavorite'])->name('files.toggleFavorite');

Route::get('/storage-info', [FileController::class, 'storageInfo'])->middleware('auth');

Route::get('/all-file', [AllFileController::class, 'AllFile'])->middleware('auth');
Route::get('/images', [ImagesController::class, 'images'])->middleware('auth');
Route::get('/docks', [DocksController::class, 'docks'])->middleware('auth');
Route::get('/video', [VideoController::class, 'videos'])->middleware('auth');
Route::get('/audio', [AudioController::class, 'audios'])->middleware('auth');
Route::get('/archive', [ArchiveController::class, 'archives'])->middleware('auth');
Route::get('/favorites', [FavoriteController::class, 'favorite'])->middleware('auth');
Route::get('/earth', [EarthController::class, 'earth'])->middleware('auth');
Route::post('/settings/update', [AuthController::class, 'updateSettings'])->name('settings.update');

