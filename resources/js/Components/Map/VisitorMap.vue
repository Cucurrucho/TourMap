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
            @tilesloaded.once="addTourModeButton"
        >
            <GMapMarker v-if="located"
                        ref="userMarker"
                        :key="-1"
                        :position="user"
                        :icon="icon"
                        @click="$toast.info('lat: ' + user.lat + ' lng: ' + user.lng)"
            />
            <GMapMarker v-for="site in sites" :key="site.id"
                        @click="$toast.info('distance between user and this: ' + distance(user.lng, user.lat, site.lng, site.lat))"
                        :position="{lat: site.lat, lng: site.lng}"></GMapMarker>
            <GMapCircle
                :options="circleOptions"
                :radius="accuracy"
                :center="user"
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

if (typeof (Number.prototype.toRad) === "undefined") {
    Number.prototype.toRad = function () {
        return this * Math.PI / 180;
    }
}
export default {
    name: "VisitorMap",
    components: {Display, Modal},
    mounted() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(this.gotLocation, this.handleError, {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            });
            this.watcherId = navigator.geolocation.watchPosition(this.moving, this.handleError, {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            });
            this.voice = this.synth.getVoices()[0];
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
                scaledSize: {width: 20, height: 20},
            },
            center: {},
            sites: [],
            currentPosition: {},
            searchDistance: 0.015,
            displayDistance: 20,
            synth: window.speechSynthesis,
            alreadySpoken: [],
            voice: null,
            touring: false,
            tourModeButton: null,
            accuracy: 0,
            circleOptions: {
                strokeColor: "#0080FE",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#0080FE",
                fillOpacity: 0.35,
            },

        }
    },
    methods: {
        gotLocation(position) {
            this.user.lat = position.coords.latitude;
            this.user.lng = position.coords.longitude;
            this.user.heading = position.coords.heading;
            this.center.lat = position.coords.latitude;
            this.center.lng = position.coords.longitude;
            this.accuracy = position.coords.accuracy;
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
            if (position.coords.accuracy < 10    ){
                this.accuracy = position.coords.accuracy;
                this.user.lat = position.coords.latitude;
                this.user.lng = position.coords.longitude;
                if (Math.abs(this.user.lat - position.coords.latitude) > this.searchDistance || Math.abs(this.user.lng - position.coords.longitude) > this.searchDistance) {
                    this.updateSites()
                }
                if (this.touring) {
                    if (!this.synth.speaking) {
                        let closeSites = [];
                        this.sites.forEach((site) => {
                            if (this.distance(site.lng, site.lat, this.user.lng, this.user.lat) < this.displayDistance) {
                                closeSites.push(site);
                            }
                        })
                        switch (closeSites.length) {
                            case 0:
                                break;
                            case 1:
                                if (this.checkHeading(position, closeSites[0])) {
                                    let site = closeSites[0];
                                    if (!this.alreadySpoken.includes(site.id)) {
                                        this.displaySite(site);
                                        this.$toast.info(site.name);
                                    }
                                }
                                break;
                            default:
                                closeSites.forEach((site) => {
                                    if (this.checkHeading(position, site)) {
                                        if (!this.alreadySpoken.includes(site.id)) {
                                            this.displaySite(site);
                                            this.$toast.info(site.name);
                                        }
                                    }
                                })
                                break;
                        }
                    }
                }
            }
        },
        updateSites() {
            router.post('visitor/markers', {position: this.user}, {
                onSuccess: () => {
                    this.sites = this.$page.props.flash.message.sites
                }
            });
        },
        displaySite(site) {
            if (this.touring) {
                const speakText = new SpeechSynthesisUtterance(site.text);
                speakText.voice = this.voice;
                speakText.onerror = (error) => {
                    this.$toast.error(error.name)
                }
                this.$toast.open({
                    message: site.text,
                    type: "info",
                    position: "top",
                    duration: 0,
                    dismissible: true
                })
                this.synth.speak(speakText);
                this.alreadySpoken.push(site.id);
            }
        },
        checkHeading(position, site) {
            let heading = position.coords.heading;
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;
            switch (true) {
                case (heading === null || isNaN(heading)):
                    break;
                case (315 <= heading || 0 <= heading < 45):
                    if (lat > site.lat) {
                        if (lng > site.lng) {
                            this.$toast.info('Site: ' + site.name + ' to the right')
                        } else {
                            this.$toast.info('Site: ' + site.name + ' to the left')
                        }
                        return true;
                    }
                    break;
                case (45 <= heading < 125):
                    if (lng > site.lng) {
                        if (lat > site.lat) {
                            this.$toast.info('Site: ' + site.name + ' to the right')
                        } else {
                            this.$toast.info('Site: ' + site.name + ' to the left')
                        }
                        return true;

                    }
                    break;
                case (125 <= heading < 225):
                    if (lat < site.lat) {
                        if (lng > site.lng) {
                            this.$toast.info('Site: ' + site.name + ' to the left')
                        } else {
                            this.$toast.info('Site: ' + site.name + ' to the right')
                        }
                        return true;

                    }
                    break;
                case (225 <= heading < 315):
                    if (lng < site.lng) {
                        if (lat > site.lat) {
                            this.$toast.info('Site: ' + site.name + ' to the left')
                        } else {
                            this.$toast.info('Site: ' + site.name + ' to the right')
                        }
                        return true;
                    }
                    break;
                default:
                    break;
            }
            return false;
        },
        addTourModeButton() {
            this.$refs.myMapRef.$mapPromise.then(async map => {
                this.tourModeButton = document.createElement("button");

                this.tourModeButton.classList.add("bg-green-500", "hover:bg-green-700", "text-white", "font-bold", "w-32", "rounded", 'text-base', 'mb-10', 'h-auto');
                this.tourModeButton.textContent = 'Start Tour';
                this.tourModeButton.type = 'button';
                this.tourModeButton.addEventListener("click", () => {
                    this.tourModeClick();
                });
                map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(this.tourModeButton); // eslint-disable-line no-undef
            });
        },
        tourModeClick() {
            if (!this.touring) {
                this.tourModeButton.textContent = 'End Tour';
                this.touring = true;
            } else {
                this.touring = false;
                this.tourModeButton.textContent = 'Start Tour';
            }
        },
        distance(lon1, lat1, lon2, lat2) {
            var R = 6371 * 1000; // Radius of the earth in km
            var dLat = (lat2 - lat1).toRad();  // Javascript functions in radians
            var dLon = (lon2 - lon1).toRad();
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1.toRad()) * Math.cos(lat2.toRad()) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            var d = R * c; // Distance in km
            return d;
        }
    },
    unmounted() {
        navigator.geolocation.clearWatch(this.watcherId)
    }
}
</script>

<style scoped>

</style>
