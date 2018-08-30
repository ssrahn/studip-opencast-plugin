<?php

class cURL
{
    const OPTION_PERMANENT = 1;
    const OPTION_DYNAMIC = 2;

    private $handler;
    private $options;

    public function __construct($options=[])
    {
        $this->init();
        if(is_array($options)){
            $this->register_options($options);
        }
    }

    private function init()
    {
        if ($this->has_handler()) {
            curl_close($this->handler);
        }
        $this->handler = curl_init();
    }

    private function has_handler()
    {
        return $this->handler != null;
    }

    public function register_option($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function register_options($options)
    {
        foreach ($options as $option_type => $option_value) {
            $this->register_option($option_type, $option_value);
        }
    }

    public function execute()
    {
        $this->set_registered_options();

        return curl_exec($this->handler);
    }

    private function set_registered_options()
    {
        curl_setopt_array($this->handler, $this->options);
    }

    public function get_info($info_key){
        return curl_getinfo($this->handler,$info_key);
    }

    public function get_http_response_code(){
        return $this->get_info(CURLINFO_HTTP_CODE);
    }

}