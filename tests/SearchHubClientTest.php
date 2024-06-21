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

        $this->config = new Config(API_KEY::API_KEY, "test", "working", "qa", "SaaS");

    }

    public function testByPassQuery1()
    {
        //SaaS "vinil click" -> "vinil click"

        $this->config->setType("SaaS");

        $query = "\"vinil click\"";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);

        $this->assertEquals(new QueryMapping("\"vinil click\"", "vinil click", null), $result);
    }

    public function testByPassQuery2()
    {
        // klick-vinyl -> \klick-vinyl
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
        // vinil click -> click-vinyl (SaaS mapper)

        $configSaaSCustomURL = new Config(API_KEY::API_KEY, "test", "working", "qa", "SaaS", "customURL");
        try {
            $query = "vinil click";
            $client = new SearchHubClient($configSaaSCustomURL);
            $result = $client->mapQuery($query);
            $expected = new QueryMapping("vinil click", "click-vinyl", null);

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
}