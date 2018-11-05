<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (12:39)
 */

interface OCModelProviderInterface
{
    public static function getDCTime($timestamp);
    public static function setWorkflowIDforCourse($id, $course, $uid, $time);
}