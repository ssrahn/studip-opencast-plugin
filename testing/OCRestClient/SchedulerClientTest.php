<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (11:34)
 */

require_once '../../classes/cURL.php';
require_once '../../classes/mock/MockcURLResponse.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/OCRestClient/OCRestClient.php';
require_once '../../classes/mock/MockDBManager.php';

require_once '../../classes/OCRestClient/SchedulerClient.php';

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SchedulerClientTest extends TestCase
{

    private $client;
    private $response;

    protected function setUp()
    {
        class_alias('MockcURL', 'OCcURL');
        class_alias('MockDBManager', 'DBManager');

        //Wird für die Erstellung des Clients benötigt, config wäre sonst fehlerhaft
        MockDBResponse::set_response(new MockDBResponse(
            'SELECT * FROM `oc_endpoints` WHERE service_type = ? AND config_id = ?',
            ['service_url', 'service_host', 'service_type', 'config_id'],
            [
                ['services', 'foo.bar/', 'services', 1]
            ]
        ));
        MockDBResponse::set_response(new MockDBResponse(
            'SELECT * FROM `oc_config` WHERE config_id = ?',
            ['service_url', 'service_user', 'service_password', 'service_version', 'config_id'],
            [
                ['foo.bar', 'matterhorn', 'OPENCAST', 1]
            ]
        ));


        $this->response[0] = '';
        //Täuscht die cURL Antworten vor
        MockcURLResponse::set_response(new MockcURLResponse(
            '/services.json',
            200,
            $this->response[0]
        ));

        $this->client = SchedulerClient::getInstance();
    }

    public function testUpdateEventForSeminar()
    {

    }

    public function testCreateEventMetadata()
    {

    }

    public function testScheduleEventForSeminar()
    {

    }

    public function testDeleteEventForSeminar()
    {

    }
}
