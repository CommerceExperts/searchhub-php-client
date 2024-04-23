<?php

use PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php';

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubRequest;

class SearchHubRequestTest extends TestCase {
    public function testRequest1()
    {
        $searchHubRequest = new SearchHubRequest("vinylclick");

        $client = new SearchHubClient("api.key", "account_name", "channel_name");
        $result = $client->optimize($searchHubRequest);
        $this->assertEquals("click-vinyl", $result->getSearchQuery());

    }

    public function testRequest2()
    {
        $searchHubRequest = new SearchHubRequest("arbeits platte");

        $client = new SearchHubClient("api.key", "account_name", "channel_name");
        $result = $client->optimize($searchHubRequest);
        $this->assertEquals("árbeitsplatten", $result->getSearchQuery());

    }

    public function testRequest3()
    {

        $searchHubRequest = new SearchHubRequest("sägenketten");

        $client = new SearchHubClient("api.key", "account_name", "channel_name");
        $result = $client->optimize($searchHubRequest);
        $this->assertEquals("kette säge", $result->getSearchQuery());
    }
}
