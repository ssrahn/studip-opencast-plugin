<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (15:29)
 */

interface TimeProviderInterface
{
    public function time();
    public function getDateTime($time='now');
}