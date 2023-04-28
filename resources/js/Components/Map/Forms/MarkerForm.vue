<template>
    <div class="p-6">
        <h1 class="text-lg font-medium text-gray-900">
            Add Site
        </h1>
        <form ref="form">
            <div class="mt-4 shadow-xl bg-gray-100 p-3 rounded-lg">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Name
                    </label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        v-model="form.name" type="text">
                    <InputError class="mt-2" :message="form.errors.name">
                    </InputError>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="type">
                        Type
                    </label>

                    <select v-model="form.type"
                            class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                        <option>Cultural</option>
                        <option>Historical</option>
                        <option>Natural</option>
                        <option>Novelty</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.type">
                    </InputError>
                </div>
                <div class="mt-2">
                    <div class="text-center mt-2"><h4>Resources</h4></div>
                    <div class="mb-4">
                        <div class="text-center mt-2"><h6>Texts</h6></div>
                        <div>
                            <div class="shadow-xl bg-gray-100 pt-2 pb-2 mb-3 rounded-lg"
                                 v-for="(text, index) in form.texts"
                                 :key="index">
                                <div class="float-right">
                                    <button type="button" @click="removeText(index)"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold rounded-full h-6 w-6">
                                        X
                                    </button>
                                </div>
                                <div class="mb-4">
                                    <div class="m-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="provider">
                                            Text
                                        </label>
                                        <textarea v-model="text.text"
                                                  class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                    </textarea>
                                    </div>

                                </div>

                            </div>
                            <button type="button" @click="addText"
                                    class="bg-green-300 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                +
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="text-center mt-2"><h6>Images</h6></div>
                        <div class="flex mt-2 flex-wrap gap-3 lg:flex-row sm:flex-col sm:justify-start justify-center">
                            <ImageUpload v-for="(image, index) in form.images" @uploaded="imageUploaded($event, index)"
                                         @removeImage="removeImage(index,image)"
                                         :starting-image="`${image.url}`" :key="index">

                            </ImageUpload>
                            <div class="flex flex-col justify-center">
                                <button type="button" @click="addImage"
                                        class="bg-green-300 hover:bg-green-700 text-white font-bold w-24 h-10 rounded">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <button :disabled="form.processing" type="button" @click="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Submit
                    </button>
                    <button v-if="startingMarker.hasOwnProperty('id') " type="button" @click="deleteMarker"
                            class="inline-flex ml-1  items-center px-4 py-2 bg-red-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import ImageUpload from "@/Components/Map/Forms/ImageUpload.vue";
import {router, useForm} from '@inertiajs/vue3'
import InputError from "@/Components/InputError.vue";

export default {
    name: "MarkerForm",
    components: {InputError, ImageUpload},
    props: {
        startingMarker: {
            type: Object,
            required: true
        },
        lat: {
            required: true,
            type: Number
        },
        lng: {
            required: true,
            type: Number
        },

    },
    data() {
        return {
            form: useForm({
                images: this.startingMarker.photos,
                texts: this.startingMarker.texts,
                type: this.startingMarker.hasOwnProperty('type') ? this.startingMarker.type : '',
                lat: this.lat,
                lng: this.lng,
                name: this.startingMarker.hasOwnProperty('name') ? this.startingMarker.name : ''
            })
        }
    },
    methods: {
        deleteMarker() {
            router.delete('sites/delete/' + this.startingMarker.id, {
                onSuccess: () => {
                    this.$emit('deleted')
                },
                onError: () => {

                }
            })
        },
        addText() {
            this.form.texts.push({provider: '', text: ''})
        },
        removeText(index) {
            this.form.texts.splice(index, 1);
        },
        addImage() {
            this.form.images.push({url: 'images/upload.png'})
        },
        removeImage(index, image) {
            this.form.images.splice(index, 1);
        },
        selectImage(index) {
            let input = this.$refs['imageInputRef' + index][0];
            input.click();
        },
        imageUploaded(file, index) {
            this.form.images[index] = file;
        },
        submit() {
            if (this.startingMarker.hasOwnProperty('id')) {
                this.form.post('/sites/edit/' + this.startingMarker.id, {
                    'Content-Type': 'multipart/form-data',
                    onSuccess: () => {
                        this.$emit('updated');

                    },
                    onError() {

                    }
                });

            } else {
                this.form.post('/sites', {
                    'Content-Type': 'multipart/form-data',
                    onSuccess: () => {
                        this.$emit('updated');
                    },
                    onError() {

                    }
                });
            }
        },
    }
}
</script>

<style scoped>

</style>
