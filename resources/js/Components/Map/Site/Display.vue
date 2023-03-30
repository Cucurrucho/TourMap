<template>
    <div class="m-6">
        <h4 class="mb-4 text-4xl font-extrabold leading-none md:text-5xl lg:text-6xl">
            {{ type }}</h4>
        <div v-if="photos.length > 0">
            <Carousel>
                <CarouselItem v-for="(photo, index) in photos" :key="index" :first="index == 0">
                    <img :src="photo.url"
                         class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
                </CarouselItem>
            </Carousel>
        </div>
    </div>
</template>

<script>
import Carousel from "@/Components/Carousel/Carousel.vue";
import {router} from "@inertiajs/vue3";
import CarouselItem from "@/Components/Carousel/CarouselItem.vue";

export default {
    name: "Display",
    components: {CarouselItem, Carousel},
    props: {
        markerId: {
            type: Number
        }
    },
    data() {
        return {
            type: '',
            photos: [],
            texts: []
        }
    },
    mounted() {
        router.post('/' + this.markerId, {}, {
            onSuccess: () => {
                this.type = this.$page.props.flash.message.type;
                this.photos = this.$page.props.flash.message.photos;
                this.texts = this.$page.props.flash.message.texts;
            }
        })
    }
}
</script>

<style scoped>

</style>
