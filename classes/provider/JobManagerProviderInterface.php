<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (15:16)
 */

interface JobManagerProviderInterface
{
    public static function job_path($id);

    public static function chunk_path($id, $chunk_number);

    public static function matterhorn_service_available();

    public static function calculate_chunk_number_from_range($range_as_string);
}