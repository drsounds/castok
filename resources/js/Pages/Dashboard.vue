<template>
    <div style="display: flex; flex-direction: column; position: absolute; left: 0; top: 0; width: 100%; height: 100%;">

        <swiper
            v-if="status == 200"
            direction="vertical"
            style="left: 0; top: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center" 
            :slides-per-view="1"
            @swiper="onSwiper"
            @slideChange="onSlideChange"
        >
            <swiper-slide
                v-for="object in feed" :key="object.id"
                :style="{
                    backgroundImage: 'url(' + object.images[0].url + ')',
                    backgroundSize: 'cover',
                    display: 'flex',
                    flexDirection: 'row',
                    alignItems: 'stretch',
                    justifyContent: 'stretch'
                }"
                @slidechange="onSlideChange"
            >
                <div :style="{color: 'white', display: 'flex', padding: '20pt', flexDirection: 'column', justifyContent: 'flex-end'}">
                    <p>{{object.name}} <span style="{opacity: 0.5}">{{object.published}}</span></p>

                </div>
                <div :style="{display: 'flex', padding: 20, flexDirection: 'column', justifyContent: 'flex-end'}">

                </div>

            </swiper-slide>
        </swiper>
        <div  v-else-if="status === 100" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center">
            <ClipLoader color="white" />
            <h1>Loading feed for you. Please wait</h1>
        </div>
        <div v-else-if="status === 9" style="flex: 1; display: flex; align-items: center; justify-content: center">
            <button class="btn btn-primary" @click="onStartFeedClicked">Start feed</button>
        </div>
        <div v-else>
            <p>Loading Spotify Web Player</p>
        </div>
        <audio ref="audio" />
    </div>

</template>

<script>
import { Swiper, SwiperSlide } from 'swiper/vue';

import { getPodcastFeed } from "../actions/feed";

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/scrollbar';
import {defineComponent, reactive, ref} from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Welcome from '@/Jetstream/Welcome.vue'
import ClipLoader from 'vue-spinner/src/ClipLoader.vue'

    export default defineComponent({

        components: {
            AppLayout,
            ClipLoader,
            Welcome,
            Swiper,
            SwiperSlide
        },
        props: {
            spotifyAccessToken: {
                type: String
            },
            spotifyDeviceId: {
                type: String
            }
        },
        setup(props, { emit }) {
            const feed =  ref([])
            const status = ref(9);
            const spotifyDeviceId = ref(null);
            const player = ref(null);
            const audio = ref(null);

 
            const onSlideChange = (swiper) => {
                const index = swiper.activeIndex;
                const episode = feed.value[index];
                if (episode) {
                    play(episode.audio_preview_url);
                }
            }
            const play = (url) => { 
                audio.value.autoPlay = true;
                audio.value.src = url;  
                audio.value.play()
                
            }
            const refresh = () => {
              status.value = 100;
                getPodcastFeed().then((result) => {
                   status.value = result.status
                   feed.value = result.objects 
                   if (result.objects.length > 0) {
                     play(result.objects[0].audio_preview_url);
                   }
                });
            }
            const onStartFeedClicked = () => {
                refresh();
            }
            return {
                feed,
                player,
                status,
                onSlideChange,
                onStartFeedClicked,
                refresh,
                audio
            }
        }
    })
</script>
