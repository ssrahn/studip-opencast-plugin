<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (13:02)
 */

class MockcURLResponse
{

    private static $responses = [];

    public static function set_response(MockcURLResponse $response)
    {
        static::$responses[static::as_id($response->url)] = $response;
    }

    public static function has_response($for_url)
    {
        foreach (static::$responses as $key=>$response){
            if($response->for_url($for_url)){
                return true;
            }
        }
        return false;
    }

    public static function for($request)
    {
        $url = $request[CURLOPT_URL];

        $plain_response = new MockcURLResponse(
            $url,
            404,
            '',
            1002,
            'Keine MockcURLResponse zur URL "' . $request[CURLOPT_URL] . '" gefunden!',
            false
        );
        if (self::has_response($url)) {
            foreach (static::$responses as $key=>$response){
                if($response->for_url($url)){
                    return $response;
                }
            }
        }

        return $plain_response;
    }

    public static function as_id($string)
    {
        return md5($string);
    }

    public static function clean($query)
    {
        $breaks_gone = str_replace(["\r\n", "\r", "\n"], '', $query);

        return preg_replace('/\s+/', ' ', $breaks_gone);
    }


    ##################################################################################################

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
        if ($this->error_number > 0 || $this->error_message != '') {
            return false;
        }

        return $this->boolean_result;
    }

    public function url_regex_ready()
    {
        $escaped_url = preg_quote($this->url, '/');

        return '/' . str_replace('\*', '.*', $escaped_url) . '/';
    }

    public function for_url($to_test)
    {
        return preg_match($this->url_regex_ready(), $to_test, $matches);
    }

    public function __toString()
    {
        $what_to_show = [
            'url'            => $this->url,
            'url_pattern'    => $this->url_regex_ready(),
            'http_code'      => $this->http_code,
            'error_number'   => $this->error_number,
            'error_message'  => $this->error_message,
            'boolean_result' => ($this->boolean_result() ? 'true' : 'false'),
            'body'           => substr($this->body, 0, 25),
            'info'           => $this->info(),
        ];

        return print_r($what_to_show, true);
    }


}