<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SongController as AdminSongController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/songs/{song}', [SongController::class, 'show'])->name('songs.show');
Route::get('/songs/{song}/stream', StreamController::class)->name('songs.stream');
Route::post('/songs/{song}/played', [InteractionController::class, 'played'])->name('songs.played')->middleware('throttle:120,1');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profiles.show');
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');
Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('albums.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profiles.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profiles.password');

    Route::get('/library/liked', [LibraryController::class, 'liked'])->name('library.liked');
    Route::get('/library/history', [LibraryController::class, 'history'])->name('library.history');

    Route::get('/my/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
    Route::get('/my/playlists/create', [PlaylistController::class, 'create'])->name('playlists.create');
    Route::post('/my/playlists', [PlaylistController::class, 'store'])->name('playlists.store');
    Route::get('/my/playlists/{playlist}/edit', [PlaylistController::class, 'edit'])->name('playlists.edit');
    Route::put('/my/playlists/{playlist}', [PlaylistController::class, 'update'])->name('playlists.update');
    Route::delete('/my/playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('playlists.destroy');
    Route::post('/my/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'addSong'])->name('playlists.songs.add');
    Route::delete('/my/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong'])->name('playlists.songs.remove');
    Route::put('/my/playlists/{playlist}/reorder', [PlaylistController::class, 'reorder'])->name('playlists.reorder');

    Route::post('/songs/{song}/like', [InteractionController::class, 'toggleLike'])->name('songs.like');
    Route::post('/songs/{song}/comments', [InteractionController::class, 'comment'])->name('songs.comments.store');
    Route::delete('/comments/{comment}', [InteractionController::class, 'deleteComment'])->name('comments.destroy');
    Route::post('/users/{user}/follow', [InteractionController::class, 'toggleFollow'])->name('users.follow');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});

Route::middleware(['auth', 'role:artist,admin'])->group(function () {
    Route::get('/studio/albums', [AlbumController::class, 'index'])->name('albums.index');
    Route::get('/studio/albums/create', [AlbumController::class, 'create'])->name('albums.create');
    Route::post('/studio/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::get('/studio/albums/{album}/edit', [AlbumController::class, 'edit'])->name('albums.edit');
    Route::put('/studio/albums/{album}', [AlbumController::class, 'update'])->name('albums.update');
    Route::delete('/studio/albums/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');

    Route::get('/studio/tracks', [SongController::class, 'mine'])->name('songs.mine');
    Route::get('/studio/tracks/create', [SongController::class, 'create'])->name('songs.create');
    Route::post('/studio/tracks', [SongController::class, 'store'])->name('songs.store');
    Route::get('/studio/tracks/{song}/edit', [SongController::class, 'edit'])->name('songs.edit');
    Route::put('/studio/tracks/{song}', [SongController::class, 'update'])->name('songs.update');
    Route::delete('/studio/tracks/{song}', [SongController::class, 'destroy'])->name('songs.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::get('/songs', [AdminSongController::class, 'index'])->name('songs.index');
    Route::put('/songs/{song}/moderate', [AdminSongController::class, 'moderate'])->name('songs.moderate');
    Route::get('/genres', [AdminGenreController::class, 'index'])->name('genres.index');
    Route::post('/genres', [AdminGenreController::class, 'store'])->name('genres.store');
    Route::put('/genres/{genre}', [AdminGenreController::class, 'update'])->name('genres.update');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::put('/reports/{report}', [AdminReportController::class, 'resolve'])->name('reports.resolve');
});
