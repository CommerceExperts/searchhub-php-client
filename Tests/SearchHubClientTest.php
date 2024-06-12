<?php

use PHPUnit\Framework\TestCase;

use SearchHub\Client\LocalMapper;
use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubConstants;
use SearchHub\Client\QueryMapping;
use SearchHub\Client\SQLCache;


class SearchHubClientTest extends TestCase
{

    protected array $config;

    public function setUp(): void
    {
        $this->config = array(
            "clientApiKey" => SearchHubConstants::API_KEY,
            "accountName" => "test",
            "channelName" => "working",
            "stage" => "qa",
        );
    }

    public function testByPassQuery1()
    {
        //SaaS "vinil click" -> "vinil click"

        $this->config["type"] = "SaaS";

        $query = "\"vinil click\"";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);

        $this->assertEquals(new QueryMapping("\"vinil click\"", "\"vinil click\"", null), $result);
    }

    public function testByPassQuery2()
    {
        // klick-vinyl -> \klick-vinyl
        $this->config["type"] = "SaaS";

        $query = "\\klick-vinyl";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);

        $this->assertEquals(new QueryMapping("\\klick-vinyl", "\\klick-vinyl", null), $result);
    }

    public function testSaaSMapper()
    {
        // vinil click -> click-vinyl (SaaS mapper)
        $this->config["type"] = "SaaS";

        $query = "vinil click";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);

        $this->assertEquals($expected, $result);
    }

    public function testCacheEmptyAndYoung()
    {
        $this->config["type"] = "local";

        $cacheMock = new \App\MappingCacheMock(true, false);
        new LocalMapper($this->config,$cacheMock);
        $this->assertTrue($cacheMock->isUpdated());
    }

    public function testCacheExistingAndYoung()
    {
        $this->config["type"] = "local";

        $cacheMock = new \App\MappingCacheMock(false, false);
        new LocalMapper($this->config,$cacheMock);
        $this->assertFalse($cacheMock->isUpdated());
    }

    public function testCacheExistingAndOld()
    {
        $this->config["type"] = "local";

        $cacheMock = new \App\MappingCacheMock(true, true);
        new LocalMapper($this->config,$cacheMock);
        $this->assertTrue($cacheMock->isUpdated());
    }

    public function testCacheEmptyAndOld()
    {
        $this->config["type"] = "local";

        $cacheMock = new \App\MappingCacheMock(false, true);
        new LocalMapper($this->config,$cacheMock);
        $this->assertTrue($cacheMock->isUpdated());
    }

    public function testCacheSQLExist()
    {
        // vinil click -> click-vinyl

        $this->config["type"] = "local";
        $query = "vinil click";

        $cacheMock = new SQLCache($this->config["accountName"], $this->config["channelName"], $this->config["stage"]);
        $mapper = new LocalMapper($this->config,$cacheMock);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);

        $this->assertEquals($expected, $result);
    }

    public function testCacheSQLEmpty()
    {
        // vinil click -> click-vinyl

        $this->config["type"] = "local";
        $query = "vinil click";

        $cacheMock = new SQLCache($this->config["accountName"], $this->config["channelName"], $this->config["stage"]);
        $cacheMock->deleteCache();
        $mapper = new LocalMapper($this->config,$cacheMock);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);
        $this->assertEquals($expected, $result);
    }

    public function testCacheFileExist()
    {
        // vinil click -> click-vinyl

        $this->config["type"] = "local";
        $query = "vinil click";

        $cacheMock = new \SearchHub\Client\FileMappingCache($this->config["accountName"], $this->config["channelName"], $this->config["stage"]);
        $mapper = new LocalMapper($this->config,$cacheMock);
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

        $this->config["type"] = "local";
        $query = "vinil click";

        $cacheMock = new \SearchHub\Client\FileMappingCache($this->config["accountName"], $this->config["channelName"], $this->config["stage"]);
        $cacheMock->deleteCache();
        $mapper = new LocalMapper($this->config,$cacheMock);
        $result = $mapper->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", null);
        $this->assertEquals($expected, $result);
    }
}