import axios from 'axios';

export async function playSpotifyTrack(uris, deviceId, token) {
    const result = axios.request({
        url:'https://api.spotify.com/v1/me/player/play?device_id=' + deviceId,
        headers: {
            'Authorization': 'Bearer ' + token
        },
        method: 'PUT'
    }, {uris});
    return result;
}

export async function toggleLikeEpisode(uris) {

    let response = await axios.put('/api/library/episodes', {
      uris
    })
    return response.data;
}