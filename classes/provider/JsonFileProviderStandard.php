<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (15:13)
 */

class JsonFileProviderStandard implements JsonFileProviderInterface
{

    public function generate($path)
    {
        return new OCJsonFile($path);
    }
}