<?php

use PHPUnit\Framework\TestCase;

class SearchHubRequestTest extends TestCase {
    public function testRequest()
    {
        $searchHubRequestApp = new test;
        $result = $searchHubRequestApp->fgets("vinylclick");
        $this->assertEquals("click-vinyl", $result);
    }
}
