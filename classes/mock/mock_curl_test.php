<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (13:56)
 */

require_once '../cURL.php';
require_once '../OCcURL.php';
require_once 'MockcURL.php';
require_once 'MockcURLRequestResponse.php';


$responses[] = new MockcURLRequestResponse(
    'foo.de/bar.php',
    404,
    '404 not found',
    1,
    'test'
);
$restricted_fields = [CURLOPT_RETURNTRANSFER];


$curl = new MockcURL($restricted_fields,$responses);
$curl->set_url('foo.de/bar.php');
$curl->set_option(CURLOPT_RETURNTRANSFER, true);

var_dump($curl->execute());
var_dump($curl->get_error_list());