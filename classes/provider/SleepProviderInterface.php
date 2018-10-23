<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (15:38)
 */

interface SleepProviderInterface
{
    public function usleep($sleep_time_ms);
}