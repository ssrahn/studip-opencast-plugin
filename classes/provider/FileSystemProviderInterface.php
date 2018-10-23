<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (15:32)
 */

interface FileSystemProviderInterface
{
    public function move_uploaded_file(string $tmp_path, string $chunk_path);

    public function unlink(string $chunk_path);
}