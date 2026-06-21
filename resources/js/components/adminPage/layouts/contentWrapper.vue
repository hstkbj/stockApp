<template>
    <div class="main-wrapper">

        <Headers/>
        <Sidebars/>

        <div class="page-wrapper">
            <RouterView />
        </div>

    </div>
</template>
<script setup>

    import Headers from './headers.vue';
    import Sidebars from './sidebars.vue';
    import { Script } from '../../plugins/script';
    import { onMounted, watch, ref } from "vue";
    import { useRoute } from "vue-router";

    const route = useRoute()

    function initializePlugins(){
        setTimeout(()=>{
            Script()
        },0)
    }

    onMounted(()=>{
        initializePlugins()
    })

    watch(route,()=>{
        initializePlugins()
    })

</script>
<style lang="">

</style>
