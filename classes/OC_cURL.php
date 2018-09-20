<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (11:46)
 */

class OC_cURL extends cURL
{
    public function __construct()
    {
        parent::__construct();
    }

    public function last_request_http_code()
    {
        return $this->get_specific_info(CURLINFO_HTTP_CODE);
    }
}