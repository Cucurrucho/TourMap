<template>
    <div class="m-6 text-center">
        <h4 class="mb-4 text-4xl font-extrabold leading-none md:text-5xl lg:text-6xl">
            {{ name }}</h4>
        <Tabs :tabs="['texts', 'photos']">
            <template v-slot:texts>
                <div v-for="(text, index) in texts" :key="index" class="mt-1 mb-1">
                    {{text.text}}
                </div>
            </template>
            <template v-slot:photos>
                <div v-if="photos.length > 0">
                    <Carousel :photos="this.photos">
                    </Carousel>
                </div>
            </template>
        </Tabs>
    </div>
</template>

<script>
import Carousel from "@/Components/Carousel/Carousel.vue";
import {router} from "@inertiajs/vue3";
import Tabs from "@/Components/Tabs/Tabs.vue";

export default {
    name: "Display",
    components: {Tabs, Carousel},
    props: {
        markerId: {
            type: Number
        }
    },
    data() {
        return {
            type: '',
            photos: [],
            texts: [],
            name: '',
            creator: ''
        }
    },
    mounted() {
        router.post('/visitor/' + this.markerId, {}, {
            onSuccess: () => {
                this.type = this.$page.props.flash.message.type;
                this.photos = this.$page.props.flash.message.photos;
                this.texts = this.$page.props.flash.message.texts;
                this.name = this.$page.props.flash.message.name;
                this.creator = this.$page.props.flash.message.creator;

            }
        })
    }
}
</script>

<style scoped>

</style>
