<?php
class ConvertConfig extends Migration
{

    function up()
    {
        $db = DBManager::get();

        // TODO: read old config data and store it in new config structure

        $db->exec("ALTER TABLE `oc_config`
            CHANGE `config_id` `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
            ADD `config` text NOT NULL,
            ADD `mkdate` datetime NOT NULL,
            ADD `chdate` datetime NOT NULL,
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
