<script setup>
import CreatorMap from '@/Components/Map/CreatorMap.vue'
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
defineProps({
    markers: {
        type: Array,
    }
});
</script>
<template>
    <Head title="Tour Map" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">My Map</h2>
        </template>
        <CreatorMap></CreatorMap>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
