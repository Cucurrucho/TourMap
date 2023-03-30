<template>
    <div>
        <GMapMap v-if="located"
                 :center="center"
                 :zoom="18"
                 map-type-id="terrain"
                 style="width: 100vw; height: 900px"
                 :options="options"
                 ref="myMapRef"
                 @tilesloaded="changeBounds"
        >
            <GMapMarker
                :key="-1"
                :position="center"
                :icon="'https://developers.google.com/maps/documentation/javascript/examples/full/images/info-i_maps.png'"
            />
            <GMapMarker :key="index"
                        v-for="(marker, index) in $page.props.markers" :position="{lat: marker.lat, lng: marker.lng}">
            </GMapMarker>
        </GMapMap>
    </div>
</template>

<script>
import {router} from "@inertiajs/vue3";

export default {
    name: "VisitorMap",
    mounted() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(this.gotLocation);
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
            loadMarkers: true
        }
    },
    methods: {
        gotLocation(position) {
            this.center.lat = position.coords.latitude;
            this.center.lng = position.coords.longitude;
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
        }
    },
    watch: {
        bounds: function () {
            router.get('/', this.bounds, {preserveState: true})
        }
    }
}
</script>

<style scoped>

</style>
