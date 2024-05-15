<?php

use PHPUnit\Framework\TestCase;
use SearchHub\Client\SearchHubClient2;
use SearchHub\Client\SearchHubConstants;
class SearchHubClient2Test extends TestCase {


    public function testStandardClientUsage() {
        $config = array(
            "clientApiKey" => SearchHubConstants::API_KEY,
            "accountName" => "test",
            "setChannelName" => "working",
            "stage" => "qa"
        );

        $client = new SearchHubClient2($config);
        $mappedQuery = $client->mapQuery("vinylclick");
        $this->assertEquals("click-vinyl", $mappedQuery);
    }

}