<template>
    <div>
        <GMapMap
            v-if="located"
            :center="center"
            :zoom="17"
            map-type-id="terrain"
            style="position: absolute; height: 100%; width: 100vw; top: 0; left: 0; z-index: 1"
            :options="options"
            ref="myMapRef"
        >
            <GMapMarker v-if="located"
                        ref="userMarker"
                        :key="-1"
                        :position="user"
                        :icon="icon"
            />
        </GMapMap>
        <div class="text-center mt-5" v-if="locationDenied">
            <div class="text-red-800 text-lg">
                This page requires location permission, if on phone please activate GPS service, refresh and give
                permission.
                If on web browser refresh and give permission
            </div>
        </div>
    </div>
</template>

<script>
import {router} from "@inertiajs/vue3";
import Display from "@/Components/Map/Site/Display.vue";
import Modal from '@/Components/Modal.vue';

export default {
    name: "VisitorMap",
    components: {Display, Modal},
    mounted() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(this.gotLocation, this.handleError);
            this.watcherId = navigator.geolocation.watchPosition(this.moving, this.handleError, {
                enableHighAccuracy: true
            });
        }
    },
    data() {
        return {
            options: {
                styles: [
                    {
                        featureType: "poi.business",
                        stylers: [{visibility: "off"}],
                    }
                ]
            },
            bounds: [],
            located: false,
            hasButton: false,
            openModal: false,
            user: {},
            watcherId: null,
            locationDenied: false,
            icon: {
                url: "https://img.icons8.com/emoji/12x/blue-circle-emoji.png",
                scaledSize: {width: 30, height: 30},
                labelOrigin: {x: 16, y: -10}
            },
            center: {},
            sites: [],
            currentPosition: {},
            searchDistance: 0.015,
            displayDistance: 0.0001,
            synth: window.speechSynthesis
        }
    },
    methods: {
        gotLocation(position) {
            this.user.lat = position.coords.latitude;
            this.user.lng = position.coords.longitude;
            this.user.heading = position.coords.heading
            this.center = this.user;
            this.located = true;
            this.locationDenied = false;
            this.updateSites();

        },
        moving(position) {
            if (this.user.lat !== position.coords.latitude) {
                this.moveUser(position);
            }
        },
        handleError(error) {
            if (error.code === error.PERMISSION_DENIED)
                this.locationDenied = true;
        },
        moveUser(position) {
            this.user.lat = position.coords.latitude;
            this.user.lng = position.coords.longitude;
            if (Math.abs(this.user.lat - position.coords.latitude) > this.searchDistance || Math.abs(this.user.lng - position.coords.longitude) > this.searchDistance) {
                this.updateSites()
            }
            if (!this.synth.speaking){
                let closeSites = [];
                this.sites.forEach((site) => {
                    if (Math.abs(site.lng - this.user.lng) < this.displayDistance || Math.abs(site.lat - this.user.lng) < this.displayDistance) {
                        closeSites.push(site);
                    }
                })
                switch (closeSites.length) {
                    case 0:
                        break;
                    case 1:
                        if (this.checkHeading(position, closeSites[0]))
                            this.displaySite(closeSites[0].text);
                        break;
                    default:
                        console.log('manySites')
                        break;
                }
            }
        },
        updateSites() {
            router.post('visitor/markers', {position: this.user}, {
                onSuccess: () => {
                    this.sites = this.$page.props.flash.message.sites;
                }
            });
        },
        async displaySite(site) {
            let voices = await this. synth.getVoices();
            const speakText = new SpeechSynthesisUtterance(site);
            speakText.voice = voices[0];
            speakText.onerror = (error) => {
                console.log(error);
            }
            this.synth.speak(speakText);
        },
        checkHeading(position, site) {
            let heading = position.coords.heading;
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;
            switch (true) {
                case (heading === null || isNaN(heading)):
                    break;
                case (0 <= heading < 90):
                    if (lat > site.lat){
                        return true;

                    }
                    break;
                case (90 <= heading <= 180):
                    if (lng > site.lng){
                        return true;

                    }
                    break;
                case (180 <= heading < 270):
                    if (lat < site.lat){
                        return true;

                    }
                    break;
                case (heading >= 270):
                    if (lng < site.lng){
                        return true;
                    }
                    break;
                default:
                    break;
            }
            return  false;
        }
    },
    unmounted() {
        navigator.geolocation.clearWatch(this.watcherId)
    }
}
</script>

<style scoped>

</style>
