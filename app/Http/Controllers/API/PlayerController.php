<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class FeedController extends Controller {
    public function index(Request $request) {
        $spotify = $request->input('spotify');
        $result = ['objects' => []];
        $savedShows = $spotify->getMySavedShows();

        for($i = 0; $i < count($savedShows); $i++) {
            $show = $savedShows[array_rand($savedShows)];
            $episodes = $spotify->getShowEpisodes($show->id);
            $episode = $episodes[array_rand($episodes)];
            $result['objects'] = $episode;
        }
        return response()->json($result);
    }
}
