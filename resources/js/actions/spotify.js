import axios from 'axios';

export async function playSpotifyTrack(uris, deviceId, token) {
    const result = axios.put('/api/player', {deviceId, uris},{
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
