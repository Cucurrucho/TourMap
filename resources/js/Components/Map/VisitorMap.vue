<template>
    <div >
        <Modal :show="openModal" @close="this.openModal = false;">
            <Display :marker-id="marker"></Display>
        </Modal>
        <GMapMap v-if="located"
                 :center="user"
                 :zoom="17"
                 map-type-id="terrain"
                 style="width: 100vw; height: 900px"
                 :options="options"
                 ref="myMapRef"
                 @tilesloaded="changeBounds"
        >
            <GMapMarker
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
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(this.gotLocation);
            this.watcherId = navigator.geolocation.watchPosition(this.setUser, this.handleError, {
                enableHighAccuracy: true
            });
        } else {
            alert('It seems like Geolocation, which is required for this page, is not enabled in your browser. Please use a browser which supports it.');
        }
    },
    data() {
        return {
            center: {},
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
            watcherId: null
        }
    },
    methods: {
        gotLocation(position) {
            console.log('here');
            this.user.lat = position.coords.latitude;
            this.user.lng = position.coords.longitude;
            this.located = true;
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
            controlUI.classList.add("bg-blue-500", "hover:bg-blue-700", "text-white", "font-bold", "py-2", "px-6", "rounded", 'text-base');
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
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(controlUI); // eslint-disable-line no-undef
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
        handleError(err) {
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
