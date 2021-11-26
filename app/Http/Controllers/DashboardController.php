<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class DashboardController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function index() {
        $spotifyUser = Session::get('spotifyUser');
        if ($spotifyUser == null) {
            return redirect('/auth/spotify/redirect');
        }
        return Inertia::render('Dashboard', [
            'spotifyAccessToken' => $spotifyUser->token
        ]);
    }
}
