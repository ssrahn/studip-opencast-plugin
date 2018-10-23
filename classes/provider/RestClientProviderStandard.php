<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (14:55)
 */

class RestClientProviderStandard implements RestClientProviderInterface
{

    public function getClientAsInstance($class_name,$course_id=null)
    {
        return $class_name::getInstance($course_id,$course_id);
    }

    public function getClientAsNew($class_name)
    {
        return new $class_name();
    }
}