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
                scaledSize: {width: 30, height: 30},
                labelOrigin: {x: 16, y: -10}
            },
            center: {},
            sites: [],
            currentPosition: {},
            searchDistance: 0.015,
            displayDistance: 0.0002,
            synth: window.speechSynthesis,
            alreadySpoken: [],
            voice: null,
            touring: false,
            tourModeButton: null

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
            if (this.touring) {
                if (!this.synth.speaking) {
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
                            if (this.checkHeading(position, closeSites[0])) {
                                let site = closeSites[0];
                                if (!this.alreadySpoken.includes(site.id)) {
                                    this.displaySite(site);
                                    this.$toast.info(site.name);
                                } else {
                                    this.$toast.warning(site.name + ' has already been viewed')
                                }
                            }
                            break;
                        default:
                            this.$toast.warning('Too many sites around');
                            break;
                    }
                } else {
                    this.$toast.warning('Already Speaking')
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
        }
    },
    unmounted() {
        navigator.geolocation.clearWatch(this.watcherId)
    }
}
</script>

<style scoped>

</style>
