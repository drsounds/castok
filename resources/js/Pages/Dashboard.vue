<template>
    <div style="display: flex; flex-direction: column; position: absolute; left: 0; top: 0; width: 100%; height: 100%;">

        <swiper
            v-if="status == 200"
            direction="vertical"
            style="left: 0; top: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center"
            :slides-per-view="1"
            @swiper="onSwiper"
            @slideChange="onSlideChange"
            @click="togglePlayPause"
        >
          <swiper-slide
              @click="togglePlayPause"
              v-for="object in feed" :key="object.id"
              :style="{position: 'relative', height: '100%'}"
              @slidechange="onSlideChange"
          >
            <div
                @click="togglePlayPause"
                :style="{
                  maskImage: 'linear-gradient(-180deg, black, transparent)',
                  backgroundImage: 'url(' + object.images[0].url + ')',
                  backgroundSize: 'cover',
                  position: 'absolute',
                  opacity: 0.5,
                  left: 0,
                  top: 0,
                  width: '100%',
                  height: '100%'
                }">
            </div>
            <div
                @click="togglePlayPause"
              :style="{

                  position: 'absolute',
                  left: 0,
                  top: 0,
                  width: '100%',
                  height: '100%',
                  backgroundSize: 'cover',
                  display: 'flex',
                  flexDirection: 'column',
              }"
            >
              <div
                  @click="togglePlayPause"
                :style="{
                  display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'stretch',
                  justifyContent: 'stretch',
                  flex: '1'
                }"
              >
                <div
                    @click="togglePlayPause" :style="{color: 'white', flex: 1, display: 'flex', borderRadius: '20pt', padding: '5pt 20pt', flexDirection: 'column', alignItems: 'flex-start', justifyContent: 'flex-end'}">
                    <img :src="object.images[0].url" :style="{width: '100%'}" />
                    <div>
                        <av-media :media="media" />
                    </div>
                    <a target="__blank" :href="`https://open.spotify.com/show/${object.show.id}`" style="font-size: 10pt; padding: 1pt 3pt; border-radius: 28pt; color: white; font-weight: bold">{{object.show.name}}</a><br>
                    <a :href="`https://open.spotify.com/episode/${object.id}`" target="__blank" >{{object.name}} <span style="{opacity: 0.5}">{{object.published}}</span></a>
                    <a  :href="`https://open.spotify.com/episode/${object.id}`" target="__blank" class="btn btn-primary">Stream full episode on on Spotify</a>
                </div>
                <div
                    @click="togglePlayPause" :style="{display: 'flex', alignItems: 'center', padding: '50pt', gap: '13pt', flex: '0 0 64pt', padding: 20, flexDirection: 'column', justifyContent: 'flex-end'}">
                   <a :href="`https://open.spotify.com/show/${object.show.id}`" target="__blank">
                    <img :src="object.show.images[0].url" style="width: 34pt; border-radius: 100%">
                  </a>
                  <button :style="{color: isLiked ? 'red' : 'white'}" :class="'ph-heart' + (isLiked ? '-fill' : '')" @click="toggleLike($event, object)" style="font-size: 30pt" />
                  <button class="ph-share" @click="share(object)" style="font-size: 30pt" />
                  <div style="height: 30pt"></div>

                </div>
              </div>
              <div
                :style="{
                  display: 'flex',
                  flexDirection: 'row',
                  alignItems: 'stretch',
                  justifyContent: 'stretch',
                  flex: '0 0 50pt'
                }"
              >
                <div
                    @click="togglePlayPause" :style="{color: 'white', flex: 1, display: 'flex', padding: '20pt', flexDirection: 'column', justifyContent: 'flex-end'}">
                  <p>Sound by {{object.show.name.substr(0, 100)}} <span style="{opacity: 0.5}">{{object.published}}</span></p>
                </div>
                <div
                    @click="togglePlayPause" :style="{display: 'flex', alignItems: 'center', padding: '50pt', gap: '13pt', flex: '0 0 64pt', padding: 20, flexDirection: 'column', justifyContent: 'flex-end'}">
                  <a :href="`https://open.spotify.com/episode/${object.id}`" target="__blank" style="padding: 20pt">
                    <img class="spinning" :src="object.images[0].url" style="width: 34pt; border-radius: 100%">
                  </a>
                </div>
              </div>
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
        <audio loop ref="audio" />
    </div>

</template>

<script>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { getPodcastFeed } from "../actions/feed";
import { toggleLikeEpisode } from '../actions/spotify';
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
            const isLiked = ref(false);

            const selectedIndex = ref(0);
            const media = ref(null);
            const constraints = { audio: true, video: false }

            const onSlideChange = (swiper) => {
                const index = swiper.activeIndex;
                selectedIndex.value = swiper.activeIndex;
                const episode = feed.value[index];
                isLiked.value = feed.value[index].isLiked;
                if (episode) {
                    play(episode.audio_preview_url);
                }
            }
            const share = (obj) => {
                const link = `https://open.spotify.com/episode/${obj.id}?utm_source=swipecast`
                if (window.navigator.webkitstartactivity instanceof Function) {
                    var params = {
                        'action': 'http://webintents.org/share',
                        'type': 'text/uri-list',
                        'data': link
                    };
                    // create the intent
                    var intent = new webkitintent(params);

                    // start the intent, and pass in the callback
                    // that is called on succes.
                    window.navigator.webkitstartactivity(intent, function (data) {
                    });
                } else {
                    window.open('https://www.facebook.com/dialog/share?app_id=2711586715813922&display=popup&href=' + encodeURIComponent(link))
                }

            }
            const togglePlayPause = () => {
                if (audio.value.paused) {
                    audio.value.play()
                } else {
                    audio.value.pause()
                }
            }
            const play = (url) => {
                audio.value.autoPlay = true;
                audio.value.src = url;
                audio.value.play()
                media.value = audio.value.srcObject

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
            const toggleLike = ($event, e) => {
                $event.stopPropagation();
                togglePlayPause()
              toggleLikeEpisode([e.uri]).then((result) => {
                feed.value[selectedIndex.value].isLiked = result.isLiked
                isLiked.value = result.isLiked
              })
            }
            const onStartFeedClicked = () => {
                refresh();
            }
            return {
                feed,
                player,
                share,
                status,
                togglePlayPause,
                onSlideChange,
                onStartFeedClicked,
                refresh,
                audio,
                isLiked,
                media,
                toggleLike
            }
        }
    })
</script>
