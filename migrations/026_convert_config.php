<?php
class ConvertConfig extends Migration
{

    function up()
    {
        $db = DBManager::get();

        // TODO: read old config data and store it in new config structure

        $db->exec("ALTER TABLE `oc_config`
            ADD `config` text NOT NULL AFTER `config_id`,
            ADD `mkdate` int NOT NULL,
            ADD `chdate` int NOT NULL,
            DROP `service_url`,
            DROP `service_user`,
            DROP `service_password`,
            DROP `service_version`,
            DROP `tos`;
        ");

        SimpleOrMap::expireTableScheme();
    }

    function down()
    {
    }

}
