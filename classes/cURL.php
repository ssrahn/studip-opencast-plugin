<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (11:33)
 */

class cURL
{
    protected $handle;
    protected $errors;
    protected $debug;

    public function __construct()
    {
        $this->init();
        $this->errors = [];
        $this->debug = false;
    }

    public function set_debug($mode)
    {
        $this->debug = $mode;
    }

    protected function init()
    {
        if (!$this->has_handle()) {
            $this->handle = curl_init();
        }
    }

    public function close()
    {
        if ($this->has_handle()) {
            curl_close($this->handle);
            $this->handle = null;
        }
    }

    public function has_handle()
    {
        return !empty($this->handle);
    }

    public function reset()
    {
        curl_reset($this->handle);
    }

    public function set_option($option_key, $option_value)
    {
        $result = $this->set_option_internal($option_key, $option_value);
        if (!$result) {
            $this->log_error([
                'number'  => 1000,
                'message' => 'Option "' . $option_key . ':' . $option_value . '" konnte nicht gesetzt werden!'
            ]);
        }

        return $result;
    }

    public function set_options($options)
    {
        $result = $this->set_options_internal($options);
        if (!$result) {
            $this->log_error([
                'number'  => 1001,
                'message' => 'Optionen konnten nicht alle gesetzt werden!'
            ]);
        }

        return $result;
    }

    public function execute()
    {
        $response = $this->execute_internal();
        $this->log_error($this->last_error());

        if ($this->has_errors()) {
            var_dump($this->get_error_list());
        }

        return $response;
    }

    public function get_info()
    {
        return $this->get_info_internal();
    }

    public function get_specific_info($key)
    {
        return $this->get_info()[$key];
    }

    protected function log_error($error)
    {
        if ($error['number'] > 0 || $error['message'] != '') {
            $this->errors[microtime()] = $error;
        }
    }

    protected function last_error()
    {
        return [
            'number'  => curl_errno($this->handle),
            'message' => curl_error($this->handle)
        ];
    }

    public function get_error_list()
    {
        return $this->errors;
    }

    public function has_errors()
    {
        return count($this->get_error_list()) > 0;
    }

    protected function set_option_internal($option_key, $option_value)
    {
        return curl_setopt($this->handle, $option_key, $option_value);
    }

    protected function set_options_internal($options)
    {
        return curl_setopt_array($this->handle, $options);
    }

    protected function execute_internal()
    {
        return curl_exec($this->handle);
    }

    protected function get_info_internal()
    {
        return curl_getinfo($this->handle);
    }
}