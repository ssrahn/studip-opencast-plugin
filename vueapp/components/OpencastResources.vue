<template>
    <div>
        <br />
        <h1>Opencast Ressourcen</h1>
        <form action="<?= PluginEngine::getLink('opencast/admin/update_resource/') ?>" method="post" class="default" v-if="resources.length">
            <fieldset class="conf-form-field">
                <legend>
                    {{ "Zuweisung der Capture Agents" | i18n }}
                </legend>

                <MessageBox type="info">
                    {{ "Jeder Capture-Agent kann nur maximal einem Raum zugewiesen werden" | i18n }}
                </MessageBox>

                <table id="oc_resourcestab" class="default">
                    <tr>
                        <th>{{ "Raum" | i18n }}</th>
                        <th>{{ "Capture Agent" | i18n }}</th>
                        <th>{{ "Workflow" | i18n }}</th>
                        <th>{{ "Status" | i18n }}</th>
                        <th>{{ "Aktionen" | i18n }}</th>
                    </tr>

                    <!--loop the ressources -->
                    <tr v-for="resource in resources.resources">
                        <td>
                            {{ resource.name }}
                        </td>
                        <td>
                            <select :name="resource.resource_id" v-if="available_agents">
                                <option value="" disabled selected>{{ "Bitte wählen Sie einen CA." | i18n }}</option>
                                <option v-for="agent in available_agents"
                                    :value="agent.name"
                                >{{ agent.name }}</option>
                            </select>
                            <span v-else>
                                {{ "Kein (weiterer) CA verfügbar" | i18n }}
                            </span>
                        </td>
                        <td>
                            <select name="workflow" v-if="available_agents && resource.definitions">
                                <option value="" disabled selected>{{ "Bitte wählen Sie einen Worflow aus." | i18n }}</option>

                                <option v-for="definition in resource.definitions"
                                    :value="definition.id"
                                >{{ definition.id }}</option>
                            </select>
                        </td>
                        <td>
                            {{ resource.agent.status }}
                        </td>
                    </tr>
                </table>
            </fieldset>

            <footer>
                <StudipButton icon="accept" @click="storeResources">
                    Speichern
                </StudipButton>
            </footer>

            <br />


            <fieldset>
                <legend>
                    {{ "Standardworkflow" | i18n }}
                </legend>

                <label>
                    {{ "Standardworkflow für Uploads:" | i18n }}
                    <select name="oc_course_uploadworkflow">
                        <? foreach ($workflows as $workflow) : ?>
                            <option value="<?= $workflow['id'] ?>" title="<?= $workflow['description'] ?>"
                                <?= ($current_workflow['workflow_id'] == $workflow['id']) ? 'selected' : '' ?>>
                                <?= $workflow['title'] ?>
                            </option>
                        <? endforeach; ?>
                        <?
                        if (!$current_workflow) {
                            echo '<option selected>' . $_('Undefiniert') . '</option>';
                        }
                        ?>
                    </select>
                </label>

                <label>
                    <input name="override_other_workflows" type="checkbox">
                    {{ "Alle anderen Workflows überschreiben" | i18n }}
                </label>

                <? if (!$current_workflow) : ?>
                    <p style="color:red">
                        {{ "Es wurde noch kein Standardworkflow definiert!" | i18n }}
                    </p>
                <? endif ?>
            </fieldset>


            <footer>
                <StudipButton icon="accept" @click="storeResources">
                    Speichern
                </StudipButton>
            </footer>
        </form>

        <MessageBox type="info" v-if="!resources.length">
            {{ "Weisen Sie Räumen die Eigenschaft für Aufzeichnungstechnik zu um hier Capture Agents zuweisen zu können." | i18n }}
        </MessageBox>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import StudipButton from "@/components/StudipButton";
import MessageBox from "@/components/MessageBox";

import {
    RESOURCES_READ, RESOURCES_UPDATE,
} from "@/store/actions.type";

export default {
    name: "OpencastResources",
    components: {
        StudipButton,
        MessageBox
    },

    data() {
        return {

        }
    },

    mounted() {
        this.$store.dispatch(RESOURCES_READ);
    },

    computed: {
        ...mapGetters(['resources']),

        availableAgents() {
            return {
                name: 'Test 1'
            }
        }

    },

    methods: {
        storeResources() {

        }
    },
}
</script>
