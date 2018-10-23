<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (12:30)
 */

class MockStudipCacheFactory
{
    public static function getCache()
    {
        return new MockStudipCache();
    }
}

class MockStudipCache
{
    public function read($name)
    {
        var_dump('tried read "'.$name.'"');
        return false;
    }

    public function write($name, $content, $expires)
    {
        var_dump('tried write "'.$name.'"');
        return true;
    }

    public function expire($name)
    {
        //nothing to do...
    }

    public function check($name)
    {
        return false;
    }
}