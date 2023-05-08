<template>
    <div>
        <Modal :show="openModal" @close="toggleModal">
            <MarkerForm :lat="formLat" :lng="formLng" @updated="toggleModal" @deleted="toggleModal"
                        :starting-marker="formMarker"></MarkerForm>
        </Modal>
        <GMapMap v-if="located"
                 :center="center"
                 :zoom="18"
                 map-type-id="terrain"
                 style="width: 100vw; height: 900px"
                 :options="options"
                 ref="myMapRef"
                 @click="addMarker"
        >
            <GMapMarker
                :key="-1"
                :position="center"
                :icon="'https://developers.google.com/maps/documentation/javascript/examples/full/images/info-i_maps.png'"
            />
            <GMapMarker :draggable="true" @dragend="(e) => dragged(e, marker.id)"
                        @click="(e) => showMarker(e,marker)" :key="index"
                        v-for="(marker, index) in $page.props.markers" :position="{lat: marker.lat, lng: marker.lng}">
            </GMapMarker>
        </GMapMap>
        <div v-if="locationDenied" class="text-red-800 text-lg">
            This page requires location permission: please give it by clicking on the lock in the browser address bar
        </div>
    </div>
</template>
<script>
import Modal from '@/Components/Modal.vue';
import MarkerForm from "@/Components/Map/Forms/MarkerForm.vue";
import {router} from '@inertiajs/vue3'


export default {

    name: "CreatorMap",
    components: {
        MarkerForm,
        Modal
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
            openModal: false,
            formLat: 0,
            formLng: 0,
            formMarker: {photos: [{url: 'images/upload.png'}], texts: [{provider: '', text: ''}]},
            bounds: [],
            located: false,
            locationDenied: false,
            markersDragged: [],
            hasButton: false
        }
    },
    mounted() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(this.gotLocation, this.handleError);
        }
        navigator.permissions
            .query({name: "geolocation"})
            .then((permissionStatus) => {
                permissionStatus.onchange = () => {
                    navigator.geolocation.getCurrentPosition(this.gotLocation);
                };
            });
    },
    methods: {
        gotLocation(position) {
            this.center.lat = position.coords.latitude;
            this.center.lng = position.coords.longitude;
            this.located = true;
            this.locationDenied = false;
        },
        addMarker(position) {
            this.formLng = position.latLng.lng();
            this.formLat = position.latLng.lat();
            this.toggleModal();
        },
        toggleModal() {
            this.openModal = !this.openModal;
        },
        showMarker(event, marker) {
            if (this.$page.props.auth.user) {
                this.formMarker = marker;
                this.formLat = marker.lat;
                this.formLng = marker.lng;
                if (!this.openModal) {
                    this.toggleModal();
                }
            }
        },
        handleError(error) {
            if (error.code === error.PERMISSION_DENIED)
                this.locationDenied = true;
        },
        dragged(event, marker) {
            this.markersDragged[marker] = event;
            if (!this.hasButton) {
                this.$refs.myMapRef.$mapPromise.then(async map => {
                    if (!this.hasButton) {
                        this.addButton(map);
                        this.hasButton = true;
                    }
                });
            }
        },
        addButton(map) {
            const controlUI = document.createElement("button");
            controlUI.classList.add("bg-red-500", "hover:bg-red-700", "text-white", "font-bold", "py-2", "px-6", "rounded", 'text-base', 'mt-5');
            controlUI.textContent = 'Save new marker position';
            controlUI.type = 'button';
            const controlText = document.createElement("div");
            controlUI.appendChild(controlText);
            controlUI.addEventListener("click", () => {
                this.hasButton = false;
                controlUI.remove();
                router.post('sites/updateMarkerPositions', {markers: this.markersDragged});
            });
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(controlUI); // eslint-disable-line no-undef
        }
    }
}
</script>

<style scoped>

</style>
