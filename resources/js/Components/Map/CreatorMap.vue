<template>
    <div>
        <Modal :show="openModal" @close="toggleModal">
            <MarkerForm :lat="formLat" :lng="formLng" @updated="newMarker" :starting-marker="formMarker"></MarkerForm>
        </Modal>
        <GMapMap
            :center="center"
            :zoom="18"
            map-type-id="terrain"
            style="width: 100vw; height: 900px"
            :options="options"
            ref="myMapRef"
            @click="addMarker"
            @tilesloaded.once="changeBounds"
        >
            <GMapMarker
                :key="-1"
                :position="center"
                :icon="'https://developers.google.com/maps/documentation/javascript/examples/full/images/info-i_maps.png'"
            />
            <GMapMarker
                @click="(e) => showMarker(e,marker)" :key="index"
                v-for="(marker, index) in $page.props.markers" :position="{lat: marker.lat, lng: marker.lng}">
            </GMapMarker>
        </GMapMap>
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
            bounds: []

        }
    },
    mounted() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(this.gotLocation);
        } else {
            alert('It seems like Geolocation, which is required for this page, is not enabled in your browser. Please use a browser which supports it.');
        }
    },
    methods: {
        gotLocation(position) {
            this.center.lat = position.coords.latitude;
            this.center.lng = position.coords.longitude;
        },
        addMarker(position) {
            this.formLng = position.latLng.lng();
            this.formLat = position.latLng.lat();
            this.toggleModal();
        },
        toggleModal() {
            this.openModal = !this.openModal;
        },

        changeBounds() {
            this.$refs.myMapRef.$mapPromise.then(async map => {
                let bounds = map.getBounds();
                this.bounds = {
                    southwest: {lat: bounds.getSouthWest().lat(), lng: bounds.getSouthWest().lng()},
                    northeast: {lat: bounds.getNorthEast().lat(), lng: bounds.getNorthEast().lng()}
                };
            })
        },
        showMarker(event, marker) {
            if (this.$page.props.auth.user) {
                this.formMarker = marker;
                this.formLat = marker.lat;
                this.formLng = marker.lng;
                if (!this.openModal) {
                    this.toggleModal();
                }
            } else {
                console.log('guest', marker)
            }
        },
        newMarker(){
            this.changeBounds();
            this.toggleModal();
        }
    },
    watch: {
        bounds: function (){
            router.get('/sites', this.bounds, {preserveState: true})
        }
    }
}
</script>

<style scoped>

</style>
