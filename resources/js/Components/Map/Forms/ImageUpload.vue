<template>
    <div>
        <div class="float-right">
            <button type="button" @click="$emit('removeImage')" class="bg-transparent  hover:bg-red-700 text-white font-bold rounded-full h-6 w-6">
                X
            </button>
        </div>
        <div
            class="imagePreviewWrapper"
            :style="{ 'background-image': `url(${previewImage})` }"
            @click="selectImage">
        </div>
        <button type="button" @click="selectImage" class="bg mt-2 bg-green-500 hover:bg-green-700 text-white font-bold w-full px-4 rounded">
            Add Image
        </button>
        <input
            class="hidden"
            ref="fileInput"
            type="file"
            @input="pickFile">
    </div>
</template>

<script>
export default {
    name: "ImageUpload",
    methods: {
        selectImage () {
            this.$refs.fileInput.click()
        },
        pickFile () {
            let input = this.$refs.fileInput
            let file = input.files
            if (file && file[0]) {
                let reader = new FileReader
                reader.onload = e => {
                    this.previewImage = e.target.result
                }
                reader.readAsDataURL(file[0])
                this.$emit('uploaded', file[0])
            }
        }
    },
    data() {
        return {
            previewImage: this.startingImage,
        };
    },
    props: {
        startingImage: {
            required: true,
            type: String
        }
    }
}
</script>

<style scoped>
.imagePreviewWrapper {
    width: 190px;
    height: 250px;
    display: block;
    cursor: pointer;
    background-size: cover;
    background-position: center center;
}
</style>
