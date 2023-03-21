<template>
    <GMapMap
        :center="center"
        :zoom="18"
        map-type-id="terrain"
        style="width: 100vw; height: 900px"
        :options="options"
    >
        <GMapMarker
            :key="1"
            :position="center"
        />
    </GMapMap>
</template>
<script>
export default {
    name: "Map",
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
            }
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
        }
    }
}
</script>

<style scoped>

</style>
