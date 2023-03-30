<script setup>
import CreatorMap from '@/Components/Map/CreatorMap.vue'
import { Head } from '@inertiajs/vue3';
import MainLayout from "@/Layouts/MainLayout.vue";
defineProps({
    markers: {
        type: Array,
    }
});
</script>
<template>
    <Head title="Tour Map" />
    <MainLayout>
        <CreatorMap></CreatorMap>
    </MainLayout>
</template>

<style scoped>

</style>
