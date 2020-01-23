<template>
    <div>
        <h1>Opencast</h1>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
//import { STATISTICS_READ_UNIQUE, STATISTICS_READ_STUDYCOURSE } from "@/store/actions.type";

export default {
    name: "Home",
    computed: {
        ...mapGetters(["unique", "studycourse"])
    },
    mounted() {

    },
    beforeRouteEnter(to, from, next) {
        next();
        /*
        Promise.all([
            store.dispatch(STATISTICS_READ_UNIQUE),
            store.dispatch(STATISTICS_READ_STUDYCOURSE)
        ]).then(() => {
            next();
        });
        */
    }
};
</script>
