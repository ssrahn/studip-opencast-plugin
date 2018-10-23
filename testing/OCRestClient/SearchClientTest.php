<?php
/**
 * @author          Jan-Frederik Leissner <jleissner@uos.de>
 * @copyright   (c) Authors
 * @version         1.0 (11:52)
 */

require_once '../../classes/cURL.php';
require_once '../../classes/mock/MockcURLResponse.php';
require_once '../../classes/mock/MockcURL.php';
require_once '../../classes/OCRestClient/OCRestClient.php';
require_once '../../classes/mock/MockDBManager.php';
require_once '../../classes/mock/MockStudipCacheFactory.php';

require_once '../../classes/OCRestClient/SearchClient.php';

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
                ['search', 'foo.bar/', 'search', 5]
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
        $this->response[0] = '{"search-results":{"total":2}}';
        MockcURLResponse::set_response(new MockcURLResponse(
            '/series.json?id=*&episodes=true&series=true',
            200,
            $this->response[0]
        ));
        $this->response[1] = '{"search-results":{"offset":"0","limit":"4","total":"4","searchTime":"2","query":"*:* AND oc_organization:mh_default_org AND (oc_acl_read:ROLE_OAUTH_USER OR oc_acl_read:ROLE_ADMIN OR oc_acl_read:ROLE_ANONYMOUS OR oc_acl_read:ROLE_USER_ADMIN OR oc_acl_read:ROLE_USER) AND -oc_mediatype:AudioVisual AND -oc_deleted:[* TO *]","result":[{"id":"74510e0a-3051-4791-b89a-3031ad07ea29","org":"mh_default_org","dcExtent":-1,"dcTitle":"Interne Weiterbildung VirtUOS","dcCreated":"2018-08-08T14:36:00+02:00","mediaType":"Series","keywords":"","modified":"2018-08-21T13:34:33.052+02:00","score":0.35818288},{"id":"f88758ee-900b-4576-84d5-a1602ee0717b","org":"mh_default_org","dcExtent":-1,"dcTitle":"Test Lehrveranstaltung","dcCreator":"Testaccount Dozent","dcPublisher":"Test Einrichtung","dcContributor":"oc-dev - Stud.IP Entwicklungsinstallation","dcCreated":"2018-07-19T11:09:00+02:00","dcLanguage":"de","dcLicense":"&copy; 2018 oc-dev - Stud.IP Entwicklungsinstallation","mediaType":"Series","keywords":"","modified":"2018-10-22T15:34:44.856+02:00","score":0.35378164},{"id":"31021bed-dcf1-4353-a6ec-1dc334c518b0","org":"mh_default_org","dcExtent":-1,"dcTitle":"Testveranstaltung","dcCreator":"Testaccount Dozent","dcPublisher":"Test Fakult","dcContributor":"Stud.IP 4.0","dcCreated":"2018-07-18T11:17:00+02:00","dcLanguage":"de","dcLicense":"&copy; 2018 Stud.IP 4.0","mediaType":"Series","keywords":"","modified":"2018-09-21T12:27:20.103+02:00","score":0.35378164},{"id":"7aaad827-7259-4428-82e2-83f9e5b7561e","org":"mh_default_org","dcExtent":-1,"dcTitle":"Praxispartner","dcCreator":"Marc Walterbusch","dcContributor":"Institut für Wirtschaftswissenschaften","dcCreated":"2018-07-04T10:14:00+02:00","dcLanguage":"de","mediaType":"Series","keywords":"","modified":"2018-08-21T13:34:32.427+02:00","score":0.35378164}]}}';
        MockcURLResponse::set_response(new MockcURLResponse(
            '/series.json',
            200,
            $this->response[1]
        ));
        $this->response[2] = '{"search-results":{"total":2,"result":[{"id":"1"},{"id":"2"},{"id":"3"}]}}';
        MockcURLResponse::set_response(new MockcURLResponse(
            '/episode.json?sid=*&q=&episodes=true&sort=&limit=0&offset=0',
            200,
            $this->response[2]
        ));

        $this->client = SearchClient::getInstance();
    }

    public function testGetBaseURL()
    {
        $this->assertEquals('search',$this->client->getBaseURL());
    }

    public function testGetSeries()
    {
        $this->assertJsonStringEqualsJsonString($this->response[0],json_encode($this->client->getSeries(1),JSON_UNESCAPED_SLASHES));
    }

    public function testGetAllSeries()
    {
        $this->assertJsonStringEqualsJsonString($this->response[1],json_encode($this->client->getAllSeries(),JSON_UNESCAPED_SLASHES));
    }

    public function testGetEpisodeCount()
    {
        $this->assertEquals(2,$this->client->getEpisodeCount(1));
    }

    public function testGetEpisodesNoCache()
    {
        $this->assertJsonStringEqualsJsonString('[{"id":"1"},{"id":"2"},{"id":"3"}]',json_encode($this->client->getEpisodes(1),JSON_UNESCAPED_SLASHES));
    }
}
