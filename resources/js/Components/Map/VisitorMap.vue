<template>
    <div>
        <Modal :show="openModal" @close="this.openModal = false;">
            <!--            <Display :marker-id="marker"></Display>-->
        </Modal>
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
            this.watcherId = navigator.geolocation.watchPosition(this.setUser, this.handleError, {
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
            marker: 0,
            openModal: false,
            user: {},
            watcherId: null,
            locationDenied: false,
            icon: {
                url: "https://img.icons8.com/emoji/12x/blue-circle-emoji.png",
                scaledSize: {width: 40, height: 40},
                labelOrigin: {x: 16, y: -10}
            },
            center: {}
        }
    },
    methods: {
        gotLocation(position) {
            this.user.lat = position.coords.latitude;
            this.user.lng = position.coords.longitude;
            this.center = this.user;
            this.located = true;
            this.locationDenied = false;
        },
        setUser(position) {
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
        }
    },
    watch: {
        bounds: function () {
            router.post('/visitor/markers', {
                bounds: this.bounds,
                // oldMarkers: this.markers
            }, {
                onSuccess: () => {
                    // this.markers = [...this.markers, ...this.$page.props.flash.message.newMarkers]
                },
                preserveState: true
            })
        }
    },
    unmounted() {
        navigator.geolocation.clearWatch(this.watcherId)
    }
}
</script>

<style scoped>

</style>
