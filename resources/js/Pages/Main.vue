<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import VisitorMap from "@/Components/Map/VisitorMap.vue";
defineProps({
    markers: {
        type: Array
    }
});
</script>
<template>
    <Head title="Tour Map" />
    <GuestLayout>
        <VisitorMap></VisitorMap>
    </GuestLayout>
</template>

<style scoped>

</style>
