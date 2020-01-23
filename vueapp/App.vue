<template>
    <div class="container" id="app">
        <div class="content">
            <router-view></router-view>
        </div>
        <div class="clearfix"></div>
        <p></p>

        <Error />
    </div>

</template>

<script>
import Error from "@/components/Error";

export default {
    name: "App",
    components: {
        Error
    },
    beforeRouteEnter(to, from, next) {
        router.push('admin');
        next();
    }
};
</script>
