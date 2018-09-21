<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (12:45)
 */

require_once '../../classes/cURL.php';
require_once '../../classes/mock/MockcURLRequestResponse.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/OCRestClient/OCRestClient.php';

use PHPUnit\Framework\TestCase;

/**
 * Class OCRestClientTest
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class OCRestClientTest extends TestCase
{

    protected function setUp()
    {
        class_alias('MockcURL', 'OCcURL');
        $this->client = new OCRestClient([
            'service_url'      => 'foo.bar/',
            'service_user'     => 'test',
            'service_password' => 'test',
            'service_version'  => 1
        ]);
    }

    public function testGetJSON()
    {
        $this->client->ochandler->set_response(new MockcURLRequestResponse(
            'foo.bar/test',
            200,
            json_encode(['worked' => true])
        ));

        $response = $this->client->getJSON('test');

        $this->assertTrue($response->worked);
    }

    public function testGetURL()
    {
        $this->client->ochandler->set_response(new MockcURLRequestResponse(
            'foo.bar/test',
            200,
            'worked!'
        ));

        $response = $this->client->getURL('test');

        $this->assertTrue($response == 'worked!');
    }

    public function testGetXML()
    {
        $this->client->ochandler->set_response(new MockcURLRequestResponse(
            'foo.bar/test',
            200,
            '<worked>true</worked>'
        ));

        $response = $this->client->getXML('test');

        $this->assertTrue($response == '<worked>true</worked>');
    }
}
