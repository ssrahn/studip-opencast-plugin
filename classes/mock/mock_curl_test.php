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

/**
 * PHP kann direkt Alias-Klassen erstellen mit 'class_alias(originalname, alias)!
 * http://php.net/manual/de/function.class-alias.php (der Befehl muckt rum)
 *
 * Besser: 'use MockcURL as OCcURL;' funktioniert einwandfrei und ersetzt die
 * Klasse ohne was anderes ändern zu müssen
 */

/**
 * GENERATING A FAKE RESPONSE
 */
$responses[] = new MockcURLRequestResponse(
    'foo.de/bar.php?id=*&name=*',
    404,
    '404 not found',
    28, #TimeoutError
    'custom error message test (real error in "number_interpreted"!'
);

echo '<pre>'.$responses[0].'</pre>';


/**
 * RESTRICT OPTION(S)
 */
$restricted_fields = [CURLOPT_RETURNTRANSFER];


/**
 * GENERATE AS USUAL
 */
$curl = new MockcURL($restricted_fields,$responses);
$curl->set_url('foo.de/bar.php?id=2&name=3');
$curl->set_option(CURLOPT_RETURNTRANSFER, true);


/**
 * TEST SOME RESULTS
 */
var_dump($curl->execute());
var_dump($curl->last_request_http_code());
var_dump($curl->get_error_list());