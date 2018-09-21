<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (12:45)
 */

require_once '../../classes/cURL.php';
require_once '../../classes/OCcURL.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/mock/MockcURLRequestResponse.php';
require_once '../../classes/OCRestClient/OCRestClient.php';

use PHPUnit\Framework\TestCase;

class OCRestClientTest extends TestCase
{
    public function testGetJSON()
    {
        $client = new OCRestClient([
            'service_url' => 'foo.bar/',
            'service_user' => 'test',
            'service_password' => 'test',
            'service_version' => 1
        ], 'MockcURL');

        $client->ochandler->set_response(new MockcURLRequestResponse(
            'foo.bar/test',
            200,
            json_encode(['worked'=>true])
        ));

        $response = $client->getJSON('test');

        $this->assertTrue($response->worked);
    }

    public function testGetURL()
    {
        $client = new OCRestClient([
            'service_url' => 'foo.bar/',
            'service_user' => 'test',
            'service_password' => 'test',
            'service_version' => 1
        ], 'MockcURL');

        $client->ochandler->set_response(new MockcURLRequestResponse(
            'foo.bar/test',
            200,
            'worked!'
        ));

        $response = $client->getURL('test');

        $this->assertTrue($response=='worked!');
    }

    public function testGetXML()
    {
        $client = new OCRestClient([
            'service_url' => 'foo.bar/',
            'service_user' => 'test',
            'service_password' => 'test',
            'service_version' => 1
        ], 'MockcURL');

        $client->ochandler->set_response(new MockcURLRequestResponse(
            'foo.bar/test',
            200,
            '<worked>true</worked>'
        ));

        $response = $client->getXML('test');

        $this->assertTrue($response=='<worked>true</worked>');
    }
}
