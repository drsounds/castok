<template>
    <div style="display: flex; flex-direction: column; position: absolute; left: 0; top: 0; width: 100%; height: 100%;">

    <swiper
        v-if="status == 200"
        style="left: 0; top: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center"    :slides-per-view="3"
        :space-between="50"
        @swiper="onSwiper"
        @slideChange="onSlideChange"
    >
        <swiper-slide
            v-for="object in feed" :key="object.id"
            :style="{
                backgroundImage: 'url(' + object.images[0].url + ')',
                display: 'flex',
                flexDirection: 'row',
                alignItems: 'stretch',
                justifyContent: 'stretch'
            }"
            @slidechange="onSlideChange"
        >
            <div :style="{display: 'flex', flexDirection: 'column', justifyContent: 'flex-end'}">
                <p>{{object.name}} <span style="{opacity: 0.5}">{{object.published}}</span></p>

            </div>
            <div :style="{display: 'flex', flexDirection: 'column', justifyContent: 'flex-end'}">

            </div>

        </swiper-slide>
    </swiper>
    <div  v-else-if="status === 100" style="left: 0; top: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center">

        <h1>Loading feed for you. Please wait</h1>
    </div>
    <div v-else-if="status === 10">
        <button @click="onStartFeedClicked">Start feed</button>
    </div>
    <div v-else>
        <p>Loading Spotify Web Player</p>
    </div>
</div>

</template>

<script>
import { Swiper, SwiperSlide } from 'swiper/vue';

import { getPodcastFeed } from "../actions/feed";

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/scrollbar';
import {defineComponent, reactive} from 'vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import Welcome from '@/Jetstream/Welcome.vue'

    export default defineComponent({

        components: {
            AppLayout,
            Welcome,
            Swiper,
            SwiperSlide
        },
        props: {
            spotifyDeviceId: {
                type: String
            }
        },
        setup(props, { emit }) {
            const feed =  reactive([])
            const status = ref(0);
            const spotifyDeviceId = ref(null);
            const player = ref(null);
            window.cb = () => {
                status.value = 9;
                window.player.value.addListener('ready', ({ device_id }) => {
                    props.deviceId = device_id
                });

                // Not Ready
                window.player.value.addListener('not_ready', ({ device_id }) => {
                    console.log('Device ID has gone offline', device_id);
                });
                window.player.connect();
            }
            const onSlideChange = (swiper) => {
                const index = swiper.activeIndex;
                const episode = feed.value[index];
                if (episode) {
                    play(episode.uri)
                }
            }
            const play = (uri) => {
                playSpotifyTrack(uri)
            }
            const refresh = () => {
                getPodcastFeed().then((result) => {
                   status.value = result.status
                   feed.value = result.objects

                });
            }
            const onStartFeedClicked = () => {
                refresh();
            }
            return {
                feed,
                player,
                status,
                onStartFeedClicked,
                refresh
            }
        }
    })
</script>
