<template>
    <div>
        <div class="inline-flex mb-4">
            <button v-for="(tab, index) in tabs"
                    :class="index === activeTab ? 'bg-indigo-500' : ''" class="capitalize border-b-4 border-indigo-500 mr-4 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l"
                    v-text="tab" @click="changeTab(index)">
            </button>
        </div>
        <div>
            <div v-for="(tab,index) in tabs" :key="index" v-show="index === activeTab">
                <slot  :name="tab"  >
                </slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Tabs",
    props: {
        tabs: {
            required: true,
            type: Array
        }
    },
    data() {
        return {
            activeTab: 0
        }
    },
    methods: {
        changeTab(index) {
            this.activeTab = index;
        }
    }
}
</script>

<style scoped>

</style>
