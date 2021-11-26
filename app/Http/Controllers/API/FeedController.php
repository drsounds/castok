<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class FeedController extends Controller {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function index(Request $request) {

        $api = new \SpotifyWebAPI\SpotifyWebAPI();
        $spotifyUser = Session::get('spotifyUser');
        if ($spotifyUser != null) {
            $api->setAccessToken($spotifyUser['token']);
        }
        $result = ['objects' => []];
        $savedShows = $api->getMySavedShows()->items;

        for($i = 0; $i < count($savedShows) && $i < 10; $i++) {
            $show = $savedShows[array_rand($savedShows)];
            $episodes = $api->getShowEpisodes($show->show->id)->items;
            if (count($episodes) > 0) {
                $episode = $episodes[array_rand($episodes)];
                $result['objects'][] = $episode;
            }
        }
        return response()->json($result);
    }
}
