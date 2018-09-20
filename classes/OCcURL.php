<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (11:46)
 */

class OCcURL extends cURL
{
    public function __construct()
    {
        parent::__construct();
    }

    public function last_request_http_code()
    {
        return $this->get_specific_info(CURLINFO_HTTP_CODE);
    }

    public function init_debug_mode()
    {
        $this->debug_stream = fopen('php://output', 'w');
        $this->set_debug(true);
        $this->set_options([
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR  => $this->debug_stream
        ]);
    }
}