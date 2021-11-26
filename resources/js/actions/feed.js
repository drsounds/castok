import axios from 'axios';

export async function getPodcastFeed() {
    try {
        let response = await axios.get('/api/feed');
        if (response.status === 200) {
            return {
                status: 200,
                objects: response.data.objects
            }
        } else {
            return {
                status: 500,
                objects: []
            }
        }
    } catch (e) {
        return {
            status: 500
        }
    }
}
