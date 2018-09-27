<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (11:16)
 */

require_once '../../classes/cURL.php';
require_once '../../classes/mock/MockcURLRequestResponse.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/OCRestClient/OCRestClient.php';
require_once '../../classes/mock/MockDBManager.php';

require_once '../../classes/OCRestClient/ArchiveClient.php';

use PHPUnit\Framework\TestCase;

/**
 * Class ArchivClientTest
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ArchiveClientTest extends TestCase
{

    protected function setUp()
    {
        class_alias('MockcURL', 'OCcURL');
        class_alias('MockDBManager', 'DBManager');

        MockDBResponse::set_response(new MockDBResponse(
            'SELECT * FROM `oc_endpoints` WHERE service_type = ? AND config_id = ?',
            ['service_url','service_host','service_type', 'config_id'],
            [
                ['test','test','test',1]
            ]
        ));

        $this->client = new ArchiveClient();
    }

    public function testApplyWorkflow()
    {
        print_r($this->client->getConfig('archive'));
    }

    public function testDeleteEvent()
    {

    }
}
