<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (15:30)
 */

class TimeProviderStandard implements TimeProviderInterface
{

    public function time()
    {
        return time();
    }

    public function getDateTime($time='now')
    {
        return new DateTime($time);
    }
}