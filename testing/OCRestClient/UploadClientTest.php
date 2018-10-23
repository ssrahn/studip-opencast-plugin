<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (13:37)
 */

require_once '../../classes/cURL.php';
require_once '../../classes/mock/MockcURLResponse.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/OCRestClient/OCRestClient.php';
require_once '../../classes/mock/MockDBManager.php';
require_once '../../classes/mock/MockStudipCacheFactory.php';

require_once '../../classes/OCRestClient/UploadClient.php';

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SearchClientTest extends TestCase
{

    private $client;
    private $response;

    protected function setUp()
    {
        class_alias('MockcURL', 'OCcURL');
        class_alias('MockDBManager', 'DBManager');
        class_alias('MockStudipCacheFactory', 'StudipCacheFactory');

        //Wird für die Erstellung des Clients benötigt, config wäre sonst fehlerhaft
        MockDBResponse::set_response(new MockDBResponse(
            'SELECT * FROM `oc_endpoints` WHERE service_type = ? AND config_id = ?',
            ['service_url', 'service_host', 'service_type', 'config_id'],
            [
                ['upload', 'foo.bar/', 'upload', 5]
            ]
        ));
        MockDBResponse::set_response(new MockDBResponse(
            'SELECT * FROM `oc_config` WHERE config_id = ?',
            ['service_url', 'service_user', 'service_password', 'service_version', 'config_id'],
            [
                ['foo.bar', 'matterhorn', 'OPENCAST', 5]
            ]
        ));

        //Täuscht die cURL Antworten vor
        $this->response[0] = '1';
        MockcURLResponse::set_response(new MockcURLResponse(
            '/newjob',
            200,
            $this->response[0]
        ));

        $this->client = UploadClient::getInstance();
    }

    public function testNewJob()
    {
        $this->assertEquals($this->response[0],$this->client->newJob(
            'test',
            500,
            10000,
            'test/flavor',
            '<mediapackage></mediapackage>'
        ));
    }

    public function testUploadChunk()
    {

    }

    public function testIsInProgress()
    {

    }

    public function testIsLastChunk()
    {

    }

    public function testGetTrackURI()
    {

    }

    public function testCheckState()
    {

    }

    public function testIsComplete()
    {

    }

    public function testGetState()
    {

    }
}
