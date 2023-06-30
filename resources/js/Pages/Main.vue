<script setup>
import { Head } from '@inertiajs/vue3';
import VisitorMap from "@/Components/Map/VisitorMap.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
</script>
<template>
    <Head title="Tour Map" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">Tour Map</h2>
        </template>
        <VisitorMap></VisitorMap>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
