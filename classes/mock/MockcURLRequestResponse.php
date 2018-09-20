<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (13:02)
 */

class MockcURLRequestResponse
{
    public $url;
    public $http_code;
    public $error_number;
    public $error_message;
    public $body;
    public $boolean_result;
    public $info;

    public function __construct($for_url, $http_code = 200, $body = '', $error_number = 0, $error_message = '', $boolean_result = true, $info = [])
    {
        $this->url = $for_url;
        $this->http_code = $http_code;
        $this->body = $body;
        $this->error_message = $error_message;
        $this->error_number = $error_number;
        $this->boolean_result = $boolean_result;
        $this->info = $info;
    }

    public function info()
    {
        $info = [];
        $info[CURLINFO_HTTP_CODE] = $this->http_code;
        foreach ($this->info as $k => $v) {
            $info[$k] = $v;
        }

        return $info;
    }

    public function boolean_result()
    {
        if($this->error_number > 0 || $this->error_message != ''){
            return false;
        }
        return $this->boolean_result;
    }

}