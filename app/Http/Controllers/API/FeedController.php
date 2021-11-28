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
        $savedShows = $api->getMySavedShows([
          'limit' => 50
        ])->items;
        shuffle($savedShows);
        for($i = 0; $i < count($savedShows) && $i < 20; $i++) {
            $show = $savedShows[array_rand($savedShows)];
            $episodes = $api->getShowEpisodes($show->show->id)->items;
            shuffle($episodes);
            if (count($episodes) > 0) {
                $episode = $episodes[array_rand($episodes)];
                $episode->isLiked = $api->myEpisodesContains([$episode->id]);
                $episode->show = $show->show;
                $result['objects'][] = $episode;
            }
        }
        shuffle($result['objects']);
        return response()->json($result);
    }
}
