<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (14:52)
 */

interface RestClientProviderInterface
{
    public function getClientAsInstance($class_name,$course_id=null);
    public function getClientAsNew($class_name);
}