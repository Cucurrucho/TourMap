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
                        @click="(event) => markerClick(event,site)"
                        :position="{lat: site.lat, lng: site.lng}"
                        ref="sites"
                        :animation="(currentlySpeaking === site.id) || (alternativeTour & distance(site.lng, site.lat, user.lng, user.lat) < displayDistance)  ? bounce : animation"
            ></GMapMarker>
            <GMapCircle
                :options="circleOptions"
                :radius="accuracy"
                :center="user"
            />
            <GMapCircle v-if="alternativeTour" :options="alternativeCircleOptions"
                        :radius="displayDistance"
                        :center="user"></GMapCircle>
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
                    },
                ],
                tap: false

            },
            bounds: [],
            located: false,
            hasButton: false,
            openModal: false,
            user: {},
            watcherId: null,
            locationDenied: false,
            center: {},
            sites: [],
            currentPosition: {},
            searchDistance: 0.015,
            displayDistance: 26,
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
            alternativeCircleOptions: {
                strokeColor: "#fa4b3e",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#fa4b3e",
                fillOpacity: 0.35,
            },
            icon: {
                fillColor: '#00F',
                fillOpacity: 0.6,
                strokeColor: '#00A',
                strokeOpacity: 0.9,
                strokeWeight: 1,
                scale: 7
            },
            animation: 'BOUNCE',
            bounce: '',
            currentlySpeaking: -1,
            alternativeModeButton: null,
            alternativeTour: false,
            click: false,
            alternativeBounce: []

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
            if (!this.synth.speaking) {
                this.currentlySpeaking = -1;
            }
            if (this.user.lat !== position.coords.latitude) {
                this.moveUser(position);
            }
        },
        handleError(error) {
            if (error.code === error.PERMISSION_DENIED)
                this.locationDenied = true;
        },

        moveUser(position) {
            let coords = position.coords;
            if (this.distance(this.center.lng, this.center.lat, coords.longitude, coords.latitude) > 1500) {
                this.updateSites()
                this.center.lng = coords.longitude;
                this.center.lat = coords.latitude;
            }
            if (this.touring) {
                this.normalTour(position);

            } else {
                this.accuracy = position.coords.accuracy;
                this.user.lat = position.coords.latitude;
                this.user.lng = position.coords.longitude;
                if (this.alternativeTour) {
                    this.alternativeTourOn(position)
                }
            }

        },
        alternativeTourOn() {
            this.sites.forEach((site) => {
                if (this.distance(site.lng, site.lat, this.user.lng, this.user.lat) < this.displayDistance) {
                    this.alternativeBounce.push(site.id)
                } else {
                    let index = this.alternativeBounce.indexOf(site.id);
                    if (index !== -1) {
                        this.alternativeBounce.splice(index, 1);
                    }
                }
            });
        },
        normalTour(position) {
            if (position.coords.accuracy <= 15) {
                this.accuracy = position.coords.accuracy;
                this.user.lat = position.coords.latitude;
                this.user.lng = position.coords.longitude;
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
        },
        updateSites() {
            router.post('visitor/markers', {position: this.user}, {
                onSuccess: () => {
                    this.sites = this.$page.props.flash.message.sites;
                }

            });
        },
        displaySite(site) {
            this.currentlySpeaking = site.id;
            const speakText = new SpeechSynthesisUtterance(site.name + " " +site.text);
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
                        return true;
                    }
                    break;
                case (45 <= heading < 125):
                    if (lng > site.lng) {
                        return true;
                    }
                    break;
                case (125 <= heading < 225):
                    if (lat < site.lat) {
                        return true;

                    }
                    break;
                case (225 <= heading < 315):
                    if (lng < site.lng) {
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
                this.icon = {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#00F',
                    fillOpacity: 0.6,
                    strokeColor: '#00A',
                    strokeOpacity: 0.9,
                    strokeWeight: 1,
                    scale: 7
                };
                this.alternativeModeButton = document.createElement('button');
                this.alternativeModeButton.classList.add("bg-green-500", "hover:bg-green-700", "text-white", "font-bold", "w-32", "rounded", 'text-base', 'mb-10', 'h-auto', 'ml-2');
                this.alternativeModeButton.textContent = 'Start Alternative Tour';
                this.alternativeModeButton.type = 'button';
                this.alternativeModeButton.addEventListener('click', () => {
                    this.alternativeModeClick();
                })
                map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(this.tourModeButton);
                map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(this.alternativeModeButton);

                this.bounce = google.maps.Animation.BOUNCE;
            });
        },
        tourModeClick() {
            if (!this.touring) {
                this.alternativeModeButton.classList.add('invisible')
                this.tourModeButton.textContent = 'End Tour';
                this.touring = true;
            } else {
                this.touring = false;
                this.currentlySpeaking = -1;
                this.synth.cancel();
                this.tourModeButton.textContent = 'Start Tour';
                this.alternativeModeButton.classList.remove('invisible')

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
        },
        alternativeModeClick() {
            if (!this.alternativeTour) {
                this.tourModeButton.classList.add('invisible')
                this.alternativeModeButton.textContent = 'End Tour';
                this.alternativeTour = true;
            } else {
                this.alternativeTour = false;
                this.currentlySpeaking = -1;
                this.synth.cancel();
                this.alternativeModeButton.textContent = 'Start Alternative Tour';
                this.tourModeButton.classList.remove('invisible')
            }
        },
        markerClick(event, site) {
            if (!this.click) {
                this.click = true;
                if (this.alternativeTour) {
                    this.displaySite(site);
                } else {
                    this.$toast.info(site.name + ' distance is ' + this.distance(site.lng, site.lat, this.user.lng, this.user.lat))
                }
            } else {
                this.click = false;
            }

        }
    },
    unmounted() {
        navigator.geolocation.clearWatch(this.watcherId)
    }
}
</script>

<style scoped>

</style>
