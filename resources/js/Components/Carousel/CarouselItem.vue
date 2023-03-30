<template>
    <div data-carousel-item :class="first ? 'relative h-56 overflow-hidden rounded-lg md:h-96' : 'duration-700 ease-in-out'">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: "CarouselItem",
    props: {
        first: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style scoped>

</style>
