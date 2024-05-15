<?php

use PHPUnit\Framework\TestCase;
use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubConstants;
class SearchHubClientTest extends TestCase {


    public function testStandardClientUsage() {
        $config = array(
            "clientApiKey" => SearchHubConstants::API_KEY,
            "accountName" => "test",
            "channelName" => "working",
            "stage" => "qa"
        );

        $client = new SearchHubClient($config);
        $this->assertEquals("click-vinyl", $client->mapQuery("vinil click"));
        $this->assertNull($client->mapQuery("aboba"));
    }

}