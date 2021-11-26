<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Laravel\Socialite\Facades\Socialite;

class FeedController extends Controller {
    public function index(Request $request) {

        $api = new \SpotifyWebAPI\SpotifyWebAPI();
        $spotifyUser = Socialite::driver('spotify')->stateless()->user();
        if ($spotifyUser != null) {
            $api->setAccessToken($spotifyUser['access_token']);
        }
        $result = ['objects' => []];
        $savedShows = $api->getMySavedShows();

        for($i = 0; $i < count($savedShows); $i++) {
            $show = $savedShows[array_rand($savedShows)];
            $episodes = $api->getShowEpisodes($show->id);
            $episode = $episodes[array_rand($episodes)];
            $result['objects'] = $episode;
        }
        return response()->json($result);
    }
}
