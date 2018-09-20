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
        return 404;
    }

    public function error_number()
    {
        return 9999;
    }

    public function error_message()
    {
        return 'Mock for url "'.$this->url.'" not found...';
    }

    public function body()
    {
        return '';
    }

    public function boolean_result()
    {
        return false;
    }

    public function info()
    {
        return [
            CURLINFO_HTTP_CODE => $this->http_code()
        ];
    }

}