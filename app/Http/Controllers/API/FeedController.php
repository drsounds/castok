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
        $savedShows = [];
        do {
        $offset = rand(0, 5) * 10;
        $keywords = [];
        try {
        $savedShows = $api->getMySavedShows([
          'limit' => 10,
          'offset' => $offset
        ])->items;
        if (count($savedShows) < 1) break;
        } catch (\Exception $e) {

        }
        shuffle($savedShows);
        for($i = 0; $i < count($savedShows) && $i < 10; $i++) {
            $show = $savedShows[array_rand($savedShows)];
            $episodes = $api->getShowEpisodes($show->show->id, ['limit' => 10])->items;
            shuffle($episodes);
            if (count($episodes) > 0) {
                $episode = $episodes[array_rand($episodes)];
                $episode->isLiked = $api->myEpisodesContains([$episode->id]);
                if ($episode->isLiked) {
                  continue;
                }
                if ($episode->resume_point->resume_position_ms > 0) {
                  continue;
                }
                $episode->show = $show->show;
                $episode->show->url = $episode->show->external_urls->spotify;
                $episode->url = $episode->external_urls->spotify;
                $result['objects'][] = $episode;
                $keywords = explode( ' ', $episode->description); 
                $episode->from = "following";
            }
        }
        $offset += 10;
        break;
      } while ($savedShows > 0); 
        for ($i = 0; $i < 3; $i++) {  
          $keyword = $keywords[array_rand($keywords)];
          $relatedEpisodes = $api->search($keyword, 'episode')->episodes->items;
          for ($i = 0; $i < 5 && $i < count($relatedEpisodes); $i++) {
            $episode =  $relatedEpisodes[$i]; 
            $episode->isLiked = $api->myEpisodesContains([$episode->id]);
            if ($episode->isLiked) {
              continue;
            }
            if ($episode->resume_point->resume_position_ms >0) {
              continue;
            }
            $episode->url = $episode->external_urls->spotify;
            $episode->show = (Object)[
              'name' => $episode->name,
              'id' => $episode->id,
              'url' => $episode->url,
              'uri' => $episode->uri,
              'images' => $episode->images
            ];
            $episode->from = "search";
            $result['objects'][] = $episode;
          }
        }
        if (count($result['objects']) > 0) {
          shuffle($result['objects']);
        }
        return response()->json($result);
    }
}
