import axios from 'axios';

export async function playSpotifyTrack(uris, deviceId, token, pos = 0) {
    const result = axios.put('/api/player', {deviceId, uris, pos},{
        headers: {
          'Authorization': 'Bearer ' + token
        }
    });
    return result;
}

export async function toggleLikeEpisode(uris) {

    let response = await axios.put('/api/library/episodes', {
      uris
    })
    return response.data;
}
