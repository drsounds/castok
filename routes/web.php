<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', 'App\\Http\\Controllers\\DashboardController@index')->name('dashboard');


Route::get('/auth/spotify/redirect', function () {
    return Socialite::driver('spotify')->stateless()->scopes([
        'user-library-read',
        'streaming',
        'app-remote-control',
        'user-read-email',
        'user-read-private',
        'user-read-playback-state',
        'user-modify-playback-state'
    ])->redirect();
});

Route::get('/auth/spotify/callback', function () {
    $user = Socialite::driver('spotify')->stateless()->user();
    Session::put('spotifyUser', [
        'token' => $user->token
    ]);
    return response()->redirectTo('/dashboard');
});
