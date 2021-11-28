<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class LikeController extends Controller {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function create(Request $request) {
 
      $api = new \SpotifyWebAPI\SpotifyWebAPI();
      $spotifyUser = Session::get('spotifyUser');
      if ($spotifyUser != null) {
          $api->setAccessToken($spotifyUser['token']);
      }
      $isLiked = false;
      $episodes = $request->input('uris');
      if ($api->myEpisodesContains($episodes)) {
        $api->deleteMyEpisodes($episodes);
      } else {
        $api->addMyEpisodes($episodes);
        $isLiked = true;
      }
      return response()->json([
        'status' => 200,
        'isLiked' => $isLiked
      ]);
    }
}
