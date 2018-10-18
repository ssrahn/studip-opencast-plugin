<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright       (c) Authors
 * @version         1.0 (11:26)
 */

require_once '../../classes/cURL.php';
require_once '../../classes/mock/MockcURLResponse.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/OCRestClient/OCRestClient.php';
require_once '../../classes/mock/MockDBManager.php';

require_once '../../classes/OCRestClient/ServicesClient.php';

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ServicesClientTest extends TestCase
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
                ['workflow', 'foo.bar/', 'services', 1]
            ]
        ));
        MockDBResponse::set_response(new MockDBResponse(
            'SELECT * FROM `oc_config` WHERE config_id = ?',
            ['service_url', 'service_user', 'service_password', 'service_version', 'config_id'],
            [
                ['foo.bar', 'matterhorn', 'OPENCAST', 1]
            ]
        ));


        $this->response[0] = '{"services":{"service":[{"type":"org.opencastproject.adminui.endpoint.AclEndpoint","host":"https://vm123.rz.uos.de","path":"/admin-ng/acl","active":true,"online":true,"maintenance":false,"jobproducer":false,"onlinefrom":"2018-08-21T13:34:28+02:00","service_state":"NORMAL","state_changed":"2018-07-03T13:19:17+02:00","error_state_trigger":0,"warning_state_trigger":0}]}}';
        //Täuscht die cURL Antworten vor
        MockcURLResponse::set_response(new MockcURLResponse(
            '/services.json',
            200,
            $this->response[0]
        ));

        $this->client = ServicesClient::getInstance();
    }

    public function testGetRESTComponents()
    {
        $this->assertJsonStringEqualsJsonString('[{"type":"org.opencastproject.adminui.endpoint.AclEndpoint","host":"https://vm123.rz.uos.de","path":"/admin-ng/acl","active":true,"online":true,"maintenance":false,"jobproducer":false,"onlinefrom":"2018-08-21T13:34:28+02:00","service_state":"NORMAL","state_changed":"2018-07-03T13:19:17+02:00","error_state_trigger":0,"warning_state_trigger":0}]',json_encode($this->client->getRESTComponents(),JSON_UNESCAPED_SLASHES));
    }
}
