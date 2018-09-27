<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (11:29)
 */

class MockDBManager
{
    public static function get($database = 'studip')
    {
        return new MockConnection();
    }
}

class MockConnection
{
    public function prepare($query)
    {
        return new MockStatement($query);
    }
}

class MockStatement
{

    public $query = '';
    public $result;

    public function __construct($query)
    {
        $this->query = MockDBResponse::clean($query);
    }

    public function execute($options = [])
    {
        $this->result = MockDBResponse::for($this->query, $options);
    }

    public function fetch($type)
    {
        $row = $this->result->rows()[0];
        if ($type == PDO::FETCH_ASSOC) {
            $current_row = [];
            for ($index = 0; $index < count($this->result->column_names()); $index++) {
                $current_row[$this->result->column_names()[$index]] = $row[$index];
            }

            return $current_row;
        }

        return $row;
    }


    public function fetchAll($type)
    {
        if ($type == PDO::FETCH_ASSOC) {
            $response = [];
            foreach ($this->result->rows() as $row) {
                $current_row = [];
                for ($index = 0; $index < count($this->result->column_names()); $index++) {
                    $current_row[$this->result->column_names()[$index]] = $row[$index];
                }
                $response[] = $current_row;
            }

            return $response;
        }

        return $this->result->return_rows;
    }
}


class MockDBResponse
{
    private static $responses = [];

    public static function set_response(MockDBResponse $response)
    {
        static::$responses[static::as_id($response->for_query)] = $response;
    }

    public static function has_response($for_query)
    {
        return isset(static::$responses[static::as_id($for_query)]);
    }

    public static function for($query, $options = [])
    {
        $query = static::clean($query);
        $plain_response = new MockDBResponse('NOT FOUND');
        if (self::has_response($query)) {
            $plain_response = static::$responses[static::as_id($query)];
        }
        $plain_response->with_options($options);

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

    #####################################################################

    private $for_query;
    private $options_important;
    private $return_columns;
    private $return_rows;

    public function __construct($for_query, $return_columns = [], $return_rows = [], $options_important = false)
    {
        $this->for_query = $for_query;
        $this->options_important = $options_important;
        $this->return_columns = $return_columns;
        $this->return_rows = $return_rows;
    }

    public function rows()
    {
        return $this->return_rows;
    }

    public function column_names()
    {
        return $this->return_columns;
    }

    public function with_options($options = [])
    {

    }
}