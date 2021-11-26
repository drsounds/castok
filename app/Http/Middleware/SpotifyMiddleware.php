<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SpotifyMiddleware extends Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $session = new \SpotifyWebAPI\Session(
            config('SPOTIFY_CLIENT_ID'),
            config('SPOTIFY_CLIENT_SECRET'),
            config('SPOTIFY_REDIRECT_URI')
        );
        $api = new \SpotifyWebAPI\SpotifyWebAPI();
        $spotifyUser = Session::get('spotify_user');
        if ($spotifyUser != null) {
            $api->setAccessToken($spotifyUser['access_token']);
        }

        $request->merge(['spotify' => $api]);

        return $next($request);
    }
}
