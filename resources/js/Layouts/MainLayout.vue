<script setup>
import {Link} from '@inertiajs/vue3';
</script>
<template>
    <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"
    >
        <div class="sm:fixed sm:top-0 sm:right-0 lg:p-4 p-6  text-right">
            <Link
                v-if="$page.props.auth.user"
                :href="route('dashboard')"
                class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
            >Dashboard
            </Link
            >
        </div>
        <div class="lg:mt-10">
            <slot></slot>
        </div>
    </div>
</template>

<style scoped>

</style>
