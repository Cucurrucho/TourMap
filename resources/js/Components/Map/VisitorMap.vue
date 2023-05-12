<template>
    <div>
        <Modal :show="openModal" @close="this.openModal = false;">
            <Display :marker-id="marker"></Display>
        </Modal>
        <GMapMap
            v-if="located"
            :center="user"
            :zoom="17"
            map-type-id="terrain"
            style="position: absolute; height: 100%; width: 100vw; top: 0; left: 0; z-index: 1"
            :options="options"
            ref="myMapRef"
            @tilesloaded="changeBounds"
        >
            <GMapMarker v-if="located"
                        ref="userMarker"
                        :key="-1"
                        :position="user"
                        animation="google.maps.Animation.DROP"
                        :icon="'https://developers.google.com/maps/documentation/javascript/examples/full/images/info-i_maps.png'"
            />
            <GMapMarker :clickable="true" @click="toggleModal(marker.id)" :key="index"
                        v-for="(marker, index) in markers" :position="{lat: marker.lat, lng: marker.lng}">
            </GMapMarker>
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
            loadMarkers: true,
            marker: 0,
            openModal: false,
            user: {},
            markers: [],
            watcherId: null,
            locationDenied: false
        }
    },
    methods: {
        gotLocation(position) {
            this.user.lat = position.coords.latitude;
            this.user.lng = position.coords.longitude;
            this.located = true;
            this.locationDenied = false;
        },
        changeBounds() {
            this.$refs.myMapRef.$mapPromise.then(async map => {
                if (this.loadMarkers) {
                    let bounds = map.getBounds();
                    this.bounds = {
                        southwest: {lat: bounds.getSouthWest().lat(), lng: bounds.getSouthWest().lng()},
                        northeast: {lat: bounds.getNorthEast().lat(), lng: bounds.getNorthEast().lng()}
                    };
                    this.loadMarkers = false;
                } else if (!this.hasButton) {
                    this.addButton(map);
                    this.hasButton = true;
                }
            })
        },
        addButton(map) {
            const controlUI = document.createElement("button");
            controlUI.classList.add("bg-blue-500", "hover:bg-blue-700", "text-white", "font-bold", "p-2", "rounded", 'text-base', 'mb-10');
            controlUI.textContent = 'Search This Area';
            controlUI.type = 'button';
            const controlText = document.createElement("div");
            controlUI.appendChild(controlText);
            controlUI.addEventListener("click", () => {
                this.loadMarkers = true;
                this.changeBounds();
                this.hasButton = false;
                controlUI.remove();
            });
            map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(controlUI); // eslint-disable-line no-undef
        },
        toggleModal(marker) {
            this.marker = marker;
            this.openModal = true;
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
                oldMarkers: this.markers
            }, {
                onSuccess: () => {
                    this.markers = [...this.markers, ...this.$page.props.flash.message.newMarkers]
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
