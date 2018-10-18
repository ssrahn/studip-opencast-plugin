<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (12:50)
 */

use PHPUnit\Framework\TestCase;

require_once '../../classes/cURL.php';
require_once '../../classes/mock/MockcURLResponse.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/OCRestClient/OCRestClient.php';
require_once '../../classes/mock/MockDBManager.php';

require_once '../../classes/OCRestClient/CaptureAgentAdminClient.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CaptureAgentAdminClientTest extends TestCase
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
                ['capture-admin', 'foo.bar/', 'capture-admin', 1]
            ]
        ));
        MockDBResponse::set_response(new MockDBResponse(
            'SELECT * FROM `oc_config` WHERE config_id = ?',
            ['service_url', 'service_user', 'service_password', 'service_version', 'config_id'],
            [
                ['foo.bar', 'matterhorn', 'OPENCAST', 1]
            ]
        ));

        //Täuscht die cURL Antworten vor
        $this->response[0] = '{"agents":{"agent":[{"name":"extron93e07","state":"offline","url":"http://131.173.58.90","time-since-last-update":3275145750,"capabilities":{"item":[{"key":"capture.device.defaults.Flavor","value":"presentation/source"},{"key":"capture.device.names","value":"defaults"},{"key":"capture.device.defaults.Encoder.Preset","value":"1080p High"},{"key":"capture.device.defaults.Layout.Preset","value":"nacst 16/9 side by side"},{"key":"capture.device.defaults.Flavor_1","value":"presenter/source"}]}}]}}';
        MockcURLResponse::set_response(new MockcURLResponse(
            'capture-admin/agents.json',
            200,
            $this->response[0]
        ));
        $this->response[1] = '{"properties-response":{"properties":{"item":[{"key":"capture.device.defaults.Flavor","value":"presentation/source"},{"key":"capture.device.names","value":"defaults"},{"key":"capture.device.defaults.Encoder.Preset","value":"1080p High"},{"key":"capture.device.defaults.Layout.Preset","value":"nacst 16/9 side by side"},{"key":"capture.device.defaults.Flavor_1","value":"presenter/source"}]}}}';
        MockcURLResponse::set_response(new MockcURLResponse(
            'capture-admin/agents/*/capabilities.json',
            200,
            $this->response[1]
        ));

        $this->client = CaptureAgentAdminClient::getInstance();
    }

    public function testGetCaptureAgents()
    {
        $this->assertJsonStringEqualsJsonString('[{"name":"extron93e07","state":"offline","url":"http://131.173.58.90","time-since-last-update":3275145750,"capabilities":{"item":[{"key":"capture.device.defaults.Flavor","value":"presentation/source"},{"key":"capture.device.names","value":"defaults"},{"key":"capture.device.defaults.Encoder.Preset","value":"1080p High"},{"key":"capture.device.defaults.Layout.Preset","value":"nacst 16/9 side by side"},{"key":"capture.device.defaults.Flavor_1","value":"presenter/source"}]}}]',
            json_encode($this->client->GetCaptureAgents(),JSON_UNESCAPED_SLASHES)
        );
    }

    public function testGetCaptureAgentCapabilities()
    {
        $this->assertJsonStringEqualsJsonString('[{"key":"capture.device.defaults.Flavor","value":"presentation/source"},{"key":"capture.device.names","value":"defaults"},{"key":"capture.device.defaults.Encoder.Preset","value":"1080p High"},{"key":"capture.device.defaults.Layout.Preset","value":"nacst 16/9 side by side"},{"key":"capture.device.defaults.Flavor_1","value":"presenter/source"}]',
            json_encode($this->client->GetCaptureAgentCapabilities('test'), JSON_UNESCAPED_SLASHES)
        );
    }
}
