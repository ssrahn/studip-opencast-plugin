<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (13:02)
 */

class MockcURLRequestResponse
{
    public $url;

    public function __construct($for_url)
    {
        $this->url = $for_url;
    }

    public function http_code()
    {
        return 200;
    }

    public function error_number()
    {
        return 0;
    }

    public function error_message()
    {
        return '';
    }

    public function body()
    {
        return '';
    }

    public function boolean_result()
    {
        return true;
    }

    public function info()
    {
        return [
            CURLINFO_HTTP_CODE => $this->http_code()
        ];
    }

}