<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (12:37)
 */

class MockcURL extends cURL
{
    public $options;
    public $restricted_options = [];
    public $last_response;

    public function __construct($restricted_options = [])
    {
        $this->init();
        $this->errors = [];
        $this->debug = false;
        $this->reset();
        $this->restricted_options = $restricted_options;
        $this->last_response = new MockcURLResponse('');
    }

    protected function init()
    {
        $this->handle = true;
    }

    public function close()
    {
        $this->handle = false;
    }

    public function has_handle()
    {
        return $this->handle;
    }

    public function reset()
    {
        $this->options = [];
    }

    protected function set_option_internal($option_key, $option_value)
    {
        if (in_array($option_key, $this->restricted_options)) {
            return false;
        }
        $this->options[$option_key] = $option_value;

        return true;
    }

    protected function set_options_internal($options)
    {
        foreach ($options as $option_key => $option_value) {
            if (!$this->set_option_internal($option_key, $option_value)) {
                return false;
            }
        }

        return true;
    }

    protected function execute_internal()
    {
        $this->last_response = $this->get_response_for_request($this->options);
        if ($this->has_option(CURLOPT_RETURNTRANSFER)) {
            if ($this->options[CURLOPT_RETURNTRANSFER]) {
                return $this->last_response->body;
            }
        }

        return $this->last_response->boolean_result();
    }

    protected function has_option($option_key)
    {
        return isset($this->options[$option_key]);
    }

    protected function last_error()
    {
        return [
            'number'  => $this->last_response->error_number,
            'message' => ($this->last_response->error_message == '' ? curl_strerror($this->last_response->error_number) : $this->last_response->error_message)
        ];
    }

    protected function get_response_for_request($request)
    {
        return MockcURLResponse::for($request);
    }

    protected function get_info_internal()
    {
        return $this->last_response->info();
    }

    public function last_request_http_code()
    {
        return $this->get_specific_info(CURLINFO_HTTP_CODE);
    }
}