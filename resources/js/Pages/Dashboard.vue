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
            v-if="object && object.images instanceof Array && object.images.length > 0"
                @click="togglePlayPause"
                :style="{
                  maskImage: 'linear-gradient(-180deg, black, transparent)',
                  backgroundImage: 'url(' + object.images[0].url + ')',
                  filter: 'blur(50pt)',
                  backgroundSize: 'cover',
                  position: 'absolute',
                  opacity: 0.8,
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
                    <img v-if="object && object.images instanceof Array && object.images.length > 0" :src="object.images[0].url" :style="{boxShadow: '2pt 2pt 28pt rgba(0, 0, 0, .5)', borderRadius: '3pt', maxWidth: '50%'}" />
                    <p class="uppecase" v-if="mode === 'spotify'">Playing episode</p>
                    <p class="uppercase" v-else>30 second preview of episode</p>
                    <a target="__blank" v-if="object.show" :href="`${object.show.url}`" style="font-size: 10pt; padding: 1pt 3pt; border-radius: 28pt; color: white; font-weight: bold">{{object.show.name}}</a>
                    <a :href="`${object.url}`" target="__blank" >{{object.name}} <span style="{opacity: 0.5}">{{object.published}}</span></a><br>
                    <a  :href="`${object.url}`" target="__blank" class="btn btn-primary">View on Spotify</a>
                </div>
                <div
                    @click="togglePlayPause" :style="{display: 'flex', alignItems: 'center', padding: '50pt', gap: '13pt', flex: '0 0 64pt', padding: 20, flexDirection: 'column', justifyContent: 'flex-end'}">

                   <a v-if="object.show" :href="`${object.show.url}`" target="__blank">
                    <img v-if="object && object.images instanceof Array && object.images.length > 0" :src="object.show.images[0].url" style="width: 34pt; border-radius: 100%">
                  </a>
                    <button :class="'ph-' + (playerState === 'playing' ? 'pause' : 'play') + '-circle'" style="font-size: 30pt" />
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
                  <p v-if="object.show">Sound by {{object.show.name.substr(0, 100)}} <span style="{opacity: 0.5}">{{object.published}}</span></p>
                </div>
                <div
                    @click="togglePlayPause" :style="{display: 'flex', alignItems: 'center', padding: '50pt', gap: '13pt', flex: '0 0 64pt', padding: 20, flexDirection: 'column', justifyContent: 'flex-end'}">
                  <a :href="`${object.url}`" target="__blank" style="padding: 20pt">
                    <img class="spinning" v-if="object && object.images instanceof Array && object.images.length > 0" :src="object.images[0].url" style="width: 34pt; border-radius: 100%">
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
        <div v-if="isLoadingMore" style="display: flex; align-items: center; justify-content: center; padding: 10pt;">
           <ClipLoader color="white" /><span>Loading more</span>
          </div>
        <audio loop ref="audio" />
    </div>

</template>

<script>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { getPodcastFeed } from "../actions/feed";
import {playSpotifyTrack, toggleLikeEpisode} from '../actions/spotify';
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
            mode: {
                type: String,
                default: 'preview'
            },
            spotifyAccessToken: {
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
            const isLoadingMore = ref(false);
            const selectedIndex = ref(0);
            const media = ref(null);
            const constraints = { audio: true, video: false }
            const playerState = ref('playing');

            const script = document.createElement('script');

            script.src = 'https://sdk.scdn.co/spotify-player.js';
            script.type = 'text/javascript';
            document.head.appendChild(script);
            window.onSpotifyWebPlaybackSDKReady = () => {
                const token = props.spotifyAccessToken
                window.player = new Spotify.Player({
                    name: 'Castok',
                    getOAuthToken: cb => { cb(token); },
                    volume: 0.5
                });
                window.player.addListener('ready', ({ device_id }) => {
                    spotifyDeviceId.value = device_id
                    status.value = 9;
                    refresh();
                });

                // Not Ready
                window.player.addListener('not_ready', ({ device_id }) => {
                    window.player.addListener('not_ready', ({ device_id }) => {
                        console.log('Device ID has gone offline', device_id);
                    });
                })
            }
            const onSlideChange = (swiper) => {
                const index = swiper.activeIndex;
                selectedIndex.value = swiper.activeIndex;
                const episode = feed.value[index];
                isLiked.value = feed.value[index].isLiked;
                
                if (episode) {
                    play(episode);
                }
                if (!isLoadingMore.value && selectedIndex.value >= feed.value.length - 1) {
                  fetchMore();
                }
            }
            const share = (obj) => {
                const link = `${obj.url}}?utm_source=swipecast`
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
                if (props.mode === 'spotify') {
                    window.player.togglePlay()
                    if (playerState.value == 'paused') {
                        playerState.value = 'playing'
                    } else {
                        playerState.value = 'paused'
                    }
                } else {
                    if (audio.value.paused) {
                        audio.value.play()
                        playerState.value = 'playing'
                    } else {
                        audio.value.pause()
                        playerState.value = 'paused'
                    }
                }
            }
            const playPreviewTrack = (url) => {
                    audio.value.autoPlay = true;
                    audio.value.src = url;
                    audio.value.play()
                    media.value = audio.value.srcObject
            }
            const play = (obj) => {
                if (props.mode === 'spotify') {
                    playSpotifyTrack([obj.uri], spotifyDeviceId.value, props.spotifyAccessToken, obj.duration_ms * 0.35).then(() => {
                    })
                } else {
                    playPreviewTrack(obj.audio_preview_url)
                }
            }
            const refresh = () => {
              status.value = 100;
                getPodcastFeed().then((result) => {
                   status.value = result.status
                   feed.value = [...feed.value, ...result.objects] 
                   console.log(feed.value);
                   if (result.objects.length > 0) {
                     play(result.objects[0]);
                   }
                });
            }
            const fetchMore = () => {
              
              isLoadingMore.value = true;
                getPodcastFeed().then((result) => {
                   status.value = result.status
                   feed.value = [...feed.value, ...result.objects] 
                   isLoadingMore.value = false;
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
                if (props.mode === 'spotify') {
                    window.player.connect()
                } else {
                    refresh()
                }
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
                isLoadingMore,
                fetchMore,
                isLiked,
                media,
                playerState,
                toggleLike
            }
        }
    })
</script>
