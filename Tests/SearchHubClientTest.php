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

    public function testByPanQuery1()
    {
        $this->config["type"] = "SaaS";

        $query = "\"vinil click\"";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);

        $this->assertEquals(new QueryMapping("\"vinil click\"", "\"vinil click\"", null), $result);
    }

    public function testByPanQuery2()
    {
        $this->config["type"] = "SaaS";

        $query = "\\klick-vinyl";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);

        $this->assertEquals(new QueryMapping("\\klick-vinyl", "\\klick-vinyl", null), $result);
    }

    public function testSaaSMapper()
    {
        $this->config["type"] = "local";

        $query = "vinil click";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", "https://duckduckgo.com/?t=ffab&q=click-vinyl&atb=v330-1&iax=images&ia=images");

        $this->assertEquals($expected, $result);
    }

    public function testSQLCacheExisting()
    {
        $this->config["type"] = "local";

        $query = "vinil click";
        $client = new SearchHubClient($this->config);
        $result = $client->mapQuery($query);
        $expected = new QueryMapping("vinil click", "click-vinyl", "https://duckduckgo.com/?t=ffab&q=click-vinyl&atb=v330-1&iax=images&ia=images");

        $this->assertEquals($expected, $result);
    }

//    public function testSQLCacheEmpty()
//    {
//    }

//    public function testFileMappingCacheExisting()
//    {
//    }

//    public function testFileMappingCacheEmpty()
//    {
//    }
}