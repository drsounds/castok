<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PlayerController extends Controller {
    public function create(Request $request) {
        $api = new \SpotifyWebAPI\SpotifyWebAPI();
        $spotifyUser = Session::get('spotifyUser');
        if ($spotifyUser != null) {
            $api->setAccessToken($spotifyUser['token']);
        }
        $deviceId = $request->input('deviceId');

        $uris = $request->input('uris');
        $pos = $request->input('pos', 0);

        $api->play($deviceId, [
            'uris' => $uris
        ]);/*
        $api->seek([
            'device_id' => $deviceId,
            'position_ms' => $pos,
        ]);*/
        return response()->json(['status' => 200]);
    }
}
