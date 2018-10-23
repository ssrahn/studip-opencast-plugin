<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (15:32)
 */

class FileSystemProviderStandard implements FileSystemProviderInterface
{

    public function move_uploaded_file(string $tmp_path, string $chunk_path)
    {
        return move_uploaded_file($tmp_path,$chunk_path);
    }

    public function unlink(string $chunk_path)
    {
        return unlink($chunk_path);
    }
}