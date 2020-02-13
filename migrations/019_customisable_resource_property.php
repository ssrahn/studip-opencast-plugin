<?php

class CustomisableResourceProperty extends Migration
{
    public function description()
    {
        return 'Connects episodes to series and not to seminars';
    }

    public function up()
    {
        $db = DBManager::get();

        if (StudipVersion::newerThan('4.4')) {
            $property_id = DBManager::get()->query("SELECT property_id FROM `resource_property_definitions`
                WHERE `name` LIKE 'OCCA#%'
                    OR `name`='Opencast Capture Agent'")->fetchColumn();

            if (!$property_id) {
                $property_id = md5(uniqid());

                DBManager::get()->query("INSERT INTO `resource_property_definitions`
                    (`property_id`, `type`, `name`, `display_name`, `options`)
                    VALUES ('$property_id' ,'bool', 'Opencast Capture Agent', 'Aufzeichnungstechnik', 'vorhanden')");
            }

        } else {
            $property_id = DBManager::get()->query("SELECT property_id FROM `resources_properties`
                WHERE `name` LIKE 'OCCA#%'
                    OR `name`='Opencast Capture Agent'")->fetchColumn();

            if (!$property_id) {
                $property_id = md5(uniqid());

                DBManager::get()->query("INSERT INTO `resources_properties`
                    (`property_id`, `name`, `description`, `type`, `options`, `system`)
                    VALUES ('$property_id', 'Opencast Capture Agent', '', 'bool', 'vorhanden', 0)");
            }
        }


        $stmt = $db->prepare('INSERT INTO config
            (field, value, section, type, `range`, mkdate, chdate, description)
            VALUES (:name, :value, :section, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');

        $stmt->execute([
            'name'        => 'OPENCAST_RESOURCE_PROPERTY_ID',
            'section'     => 'opencast',
            'description' => 'ID für die Eigenschaft eines Raumes, die angibt ob es Aufzeichnungstechnik gibt.',
            'range'       => 'global',
            'type'        => 'string',
            'value'       => $property_id
        ]);

        $db->exec("DELETE FROM `oc_config_precise`
            WHERE name = 'capture_agent_attribute'");

        SimpleOrMap::expireTableScheme();
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM config
            WHERE field = 'OPENCAST_RESOURCE_PROPERTY_ID'");
    }
}
