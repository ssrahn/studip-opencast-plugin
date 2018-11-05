<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (12:42)
 */

class OCCourseModelProviderStandard implements OCCourseModelProviderInterface
{

    public function create($course)
    {
        return new OCCourseModel($course);
    }
}