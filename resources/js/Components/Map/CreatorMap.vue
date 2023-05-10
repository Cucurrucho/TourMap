<template>
    <div>
        <Modal :show="openModal" @close="toggleModal">
            <MarkerForm :lat="formLat" :lng="formLng" @updated="toggleModal" @deleted="toggleModal"
                        :starting-marker="formMarker"></MarkerForm>
        </Modal>
        <GMapMap
            :center="center"
            :zoom="15"
            map-type-id="terrain"
            style="position: absolute; height: 100%; width: 100vw; top: 0; left: 0; z-index: 1"
            :options="options"
            ref="myMapRef"
            @click="addMarker"
        >
            <GMapMarker
                :key="-1"
                :position="center"
                v-if="located"
                :icon="'https://developers.google.com/maps/documentation/javascript/examples/full/images/info-i_maps.png'"
            />
            <GMapMarker :draggable="editMode" @dragend="(e) => dragged(e, marker.id)"
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
            center: {lat: 32.801987378218094, lng: 35.00797829055788},
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
            hasButton: false,
            editModeButton: {},
            editMode: false
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
        this.$refs.myMapRef.$mapPromise.then(async map => {
            this.editModeButton = document.createElement("button");
            this.editModeButton.classList.add("bg-green-500", "hover:bg-green-700", "text-white", "font-bold", "p-2" ,"rounded", 'text-base', 'mb-10');
            this.editModeButton.textContent = 'Edit Mode';
            this.editModeButton.type = 'button';
            this.editModeButton.addEventListener("click", () => {
                this.editModeClick();
            });
            map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(this.editModeButton); // eslint-disable-line no-undef
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
            this.formMarker = {photos: [{url: 'images/upload.png'}], texts: [{provider: '', text: ''}]};
        },
        showMarker(event, marker) {
            if (this.$page.props.auth.user && this.editMode) {
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
        },
        editModeClick() {
            if (!this.editMode) {
                this.editModeButton.textContent = "Save and exit edit mode";
                this.editMode = true;
            } else {
                router.post('sites/updateMarkerPositions', {markers: this.markersDragged}, {
                    onSuccess: () => {
                        this.$toast.success('Markers Edited Successfully', {
                            position: "top-right"
                        })
                    }
                });
                this.editModeButton.textContent = "Edit Mode";
                this.editMode = false;
            }
        }
    }
}
</script>

<style scoped>

</style>
