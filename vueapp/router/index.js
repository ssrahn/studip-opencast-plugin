import Vue from "vue";
import Router from "vue-router";
import Home from "@/views/Home"

Vue.use(Router);

export default new Router({
    routes: [
        {
            name: "home",
            path: "/home",
            component: () => import("@/views/Home")
        },
        {
            name: "admin",
            path: "/admin",
            component: () => import("@/views/Admin")
        },
    ]
});
