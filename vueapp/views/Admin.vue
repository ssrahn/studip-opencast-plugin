<template>
    <div>
        <form class="default">
            <fieldset>
                <legend>
                    {{ "Opencast Server Einstellungen" | i18n }}
                </legend>

                <label>
                    {{ "Basis URL zur Opencast Installation" | i18n }}
                    <input type="text"
                        v-model="config.url"
                        placeholder="http://opencast.url">
                </label>

                <label>
                    {{ "Nutzerkennung" | i18n }}
                    <input type="text"
                        v-model="config.user"
                        placeholder="ENDPOINT_USER">
                </label>

                <label>
                    {{ "Passwort" | i18n }}
                    <input type="password"
                        v-model="config.password"
                        placeholder="ENDPOINT_USER_PASSWORD">
                </label>

                <label>
                    {{ "LTI Consumerkey" | i18n }}
                    <input type="text"
                        v-model="config.ltikey"
                        placeholder="CONSUMERKEY">
                </label>

                <label>
                    {{ "LTI Consumersecret" | i18n }}
                    <input type="text"
                        v-model="config.ltisecret"
                        placeholder="CONSUMERSECRET">
                </label>
            </fieldset>

            <footer>
                <StudipButton icon="accept"
                    @click="storeConfig"
                >Einstellungen überprüfen</StudipButton>
            </footer>
        </form>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import StudipButton from "@/components/StudipButton";
import {
    CONFIG_READ, CONFIG_UPDATE,
    CONFIG_CREATE, CONFIG_DELETE
} from "@/store/actions.type";

export default {
    name: "Home",
    components: {
        StudipButton
    },
    computed: {
        ...mapGetters(['config'])
    },
    mounted() {

    },
    methods: {
        storeConfig() {
            console.log('button clicked');
            this.$store.dispatch(CONFIG_CREATE, {
                config: this.config
            });
        }
    },
    beforeRouteEnter(to, from, next) {
        Promise.all([
            store.dispatch(CONFIG_READ, 1)
        ]).then(() => {
            next();
        });
    }
};
</script>
