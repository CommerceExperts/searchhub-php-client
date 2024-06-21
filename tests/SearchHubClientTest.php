<?php

use App\MappingCacheMock;
use PHPUnit\Framework\TestCase;

use SearchHub\Client\API_KEY;
use SearchHub\Client\Config;
use SearchHub\Client\FileMappingCache;
use SearchHub\Client\LocalMapper;
use SearchHub\Client\SearchHubClient;
use SearchHub\Client\QueryMapping;
use SearchHub\Client\SQLCache;


class SearchHubClientTest extends TestCase
{

    protected \SearchHub\Client\Config $config;

    public function setUp(): void
    {

        $this->config = new Config( "test", "working", "qa", "SaaS", null, API_KEY::API_KEY);

    }

    public function testByPassQuery1()
    {
        //SaaS "vinil click" -> vinil click

        $this->config->setType("SaaS");

        $query = "\"vinil click\"";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);

        $this->assertEquals(new QueryMapping("\"vinil click\"", "vinil click", null), $result);
    }

    public function testByPassQuery2()
    {
        // \klick-vinyl -> \klick-vinyl
        $this->config->setType("SaaS");

        $query = "\\klick-vinyl";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);

        $this->assertEquals(new QueryMapping("\\klick-vinyl", "\\klick-vinyl", null), $result);
    }

    public function testSaaSMapper()
    {
        // vinil click -> click-vinyl (SaaS mapper)
        $this->config->setType("SaaS");

        $query = "vinil click";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);

        $this->assertEquals($expected, $result);
    }

    public function testSaaSMapperCustomURI()
    {


        $configSaaSCustomURL = new Config("test", "working", "qa", "saas",
        "https://saas.searchhub.io/smartquery/v2/decathlon/de");
        try {
            $query = "farrad";
            $client = new SearchHubClient($configSaaSCustomURL);
            $result = $client->mapQuery($query);
            $expected = new QueryMapping("farrad", "fahrrad", "https://www.decathlon.de/Fahrrad-Welt_lp-QSM56N");

            $this->assertEquals($expected, $result);
        } catch (\Exception $e) {
            $this->markTestSkipped("Failed to connect to the server with custom uri: {$configSaaSCustomURL->getSaaSEndPoint()}");
        }
    }

    public function testSaaSMapperCustomURI2()
    {
        // vinil click -> click-vinyl (SaaS mapper)

        $configSaaSCustomURL = new Config("test", "working", "qa", "saas",
            "https://saas.searchhub.io/smartquery/v2/demo/de");

        try {
            $query = "m.e.t.a.l.";
            $client = new SearchHubClient($configSaaSCustomURL);
            $result = $client->mapQuery($query);

            $expected = new QueryMapping("m.e.t.a.l.", "metal", null);

            $this->assertEquals($expected, $result);
        } catch (\Exception $e) {
            $this->markTestSkipped("Failed to connect to the server with custom uri: {$configSaaSCustomURL->getSaaSEndPoint()}");
        }
    }

    public function testCacheEmptyAndYoung()
    {
        $this->config->setType("local");

        $cacheMock = new MappingCacheMock(true, false);
        new LocalMapper($this->config,$cacheMock);
        $this->assertTrue($cacheMock->isUpdated());
    }

    public function testCacheExistingAndYoung()
    {
        $this->config->setType("local");

        $cacheMock = new MappingCacheMock(false, false);
        new LocalMapper($this->config,$cacheMock);
        $this->assertFalse($cacheMock->isUpdated());
    }

    public function testCacheExistingAndOld()
    {
        $this->config->setType("local");

        $cacheMock = new MappingCacheMock(true, true);
        new LocalMapper($this->config,$cacheMock);
        $this->assertTrue($cacheMock->isUpdated());
    }

    public function testCacheEmptyAndOld()
    {
        $this->config->setType("local");

        $cacheMock = new MappingCacheMock(false, true);
        new LocalMapper($this->config,$cacheMock);
        $this->assertTrue($cacheMock->isUpdated());
    }

    public function testCacheSQLExist()
    {
        // vinil click -> click-vinyl

        $this->config->setType("local");
        $query = "vinil click";

        $SQLCache = new SQLCache($this->config);
        $mapper = new LocalMapper($this->config, $SQLCache);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);

        $this->assertEquals($expected, $result);
    }

    public function testCacheSQLEmpty()
    {
        // vinil click -> click-vinyl

        $this->config->setType("local");
        $query = "vinil click";

        $SQLCache = new SQLCache($this->config);
        $SQLCache->deleteCache();
        $mapper = new LocalMapper($this->config, $SQLCache);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);

        $this->assertEquals($expected, $result);
    }

    public function testCacheFileExist()
    {
        // vinil click -> click-vinyl

        $this->config->setType("local");
        $query = "vinil click";

        $fileCache = new FileMappingCache($this->config);
        $mapper = new LocalMapper($this->config,$fileCache);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);

        $this->assertEquals($expected, $result);
    }

    /**
     * @throws Exception
     */
    public function testCacheFileEmpty()
    {
        // vinil click -> click-vinyl

        $this->config->setType("local");
        $query = "vinil click";

        $fileCache = new FileMappingCache($this->config);
        $fileCache->deleteCache();
        $mapper = new LocalMapper($this->config,$fileCache);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);
        $this->assertEquals($expected, $result);
    }

    public function testSaaSMapperAwfulQuery()
    {
        // aboba -> aboba (SaaS mapper)
        $this->config->setType("SaaS");

        $query = "aboba";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);
        $expected = new QueryMapping("aboba", "aboba", null);

        $this->assertEquals($expected, $result);
    }

    public function testCacheSQLExistAwfulQuery()
    {
        // aboba -> aboba

        $this->config->setType("local");
        $query = "aboba";

        $SQLCache = new SQLCache($this->config);
        $mapper = new LocalMapper($this->config, $SQLCache);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("aboba", null, null);

        $this->assertEquals($expected, $result);
    }

    public function testCacheSQLEmptyAwfulQuery()
    {
        // aboba -> aboba

        $this->config->setType("local");
        $query = "aboba";

        $SQLCache = new SQLCache($this->config);
        $SQLCache->deleteCache();
        $mapper = new LocalMapper($this->config, $SQLCache);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("aboba", "aboba", null);

        $this->assertEquals($expected, $result);
    }

    public function testCacheFileExistAwfulQuery()
    {
        // aboba -> aboba

        $this->config->setType("local");
        $query = "aboba";

        $fileCache = new FileMappingCache($this->config);
        $mapper = new LocalMapper($this->config,$fileCache);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("aboba", "aboba", null);

        $this->assertEquals($expected, $result);
    }

    /**
     * @throws Exception
     */
    public function testCacheFileEmptyAwfulQuery()
    {
        // aboba -> aboba

        $this->config->setType("local");
        $query = "aboba";

        $fileCache = new FileMappingCache($this->config);
        $fileCache->deleteCache();
        $mapper = new LocalMapper($this->config,$fileCache);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("aboba", "aboba", null);
        $this->assertEquals($expected, $result);
    }
}