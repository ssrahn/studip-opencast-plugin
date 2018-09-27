<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (14:07)
 */

require_once '../../classes/cURL.php';
require_once '../../classes/mock/MockcURLResponse.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/OCRestClient/OCRestClient.php';
require_once '../../classes/mock/MockDBManager.php';

require_once '../../classes/OCRestClient/WorkflowClient.php';

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WorkflowClientTest extends TestCase
{

    private $client;
    private $json_responses = [];

    protected function setUp()
    {
        class_alias('MockcURL', 'OCcURL');
        class_alias('MockDBManager', 'DBManager');

        //Wird für die Erstellung des Clients benötigt, config wäre sonst fehlerhaft
        MockDBResponse::set_response(new MockDBResponse(
            'SELECT * FROM `oc_endpoints` WHERE service_type = ? AND config_id = ?',
            ['service_url', 'service_host', 'service_type', 'config_id'],
            [
                ['workflow', 'foo.bar/', 'workflow', 1]
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
        $this->json_responses[0] = '{"definitions":{"definition":[{"id":1,"tags":{"tag":["tag_1","tag_2"]},"description":"test_desc","title":"test_title"},{"id":2,"tags":{"tag":["tag_1","tag_2"]},"description":"test_desc","title":"test_title"}]}}';
        MockcURLResponse::set_response(
            new MockcURLResponse(
                'definitions.json', 200,
                $this->json_responses[0]
            )
        );
        MockcURLResponse::set_response(
            new MockcURLResponse('/remove/*', 204)
        );
        $this->json_responses[1] = '{"workflows":{"workflow":[{"id":1},{"id":2}]}}';
        MockcURLResponse::set_response(
            new MockcURLResponse(
                '/instances.json?state=&q=&seriesId=*&seriesTitle=&creator=&contributor=&fromdate=&todate=&language=&license=&title=&subject=&workflowdefinition=&mp=&op=&sort=&startPage=0&count=1000&compact=true',
                200, $this->json_responses[1]
            )
        );
        MockcURLResponse::set_response(
            new MockcURLResponse('workflow/instance/*.json', 200, '{"workflow":{"worked":true}}')
        );


        $this->client = WorkflowClient::getInstance();
    }

    public function testGetWorkflowInstance()
    {
        $this->assertTrue($this->client->getWorkflowInstance(1)->worked);
    }

    public function testGetDefinitions()
    {
        $this->assertJsonStringEqualsJsonString($this->json_responses[0], json_encode($this->client->getDefinitions(1)));
    }

    public function testGetInstances()
    {
        $this->assertJsonStringEqualsJsonString($this->json_responses[1], json_encode($this->client->getInstances(1)));
    }

    public function testRemoveInstanceComplete()
    {
        $this->assertTrue($this->client->removeInstanceComplete(1));
    }

    public function testGetTaggedWorkflowDefinitions()
    {
        $this->assertCount(2, $this->client->getTaggedWorkflowDefinitions());
    }
}
