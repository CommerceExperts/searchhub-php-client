<?php

use PHPUnit\Framework\TestCase;

use SearchHub\Client\Config;
use SearchHub\Client\QueryMapping;
use SearchHub\Client\SQLCache;


class SQLCacheTest extends TestCase
{

    public function testLoadingCache() : void
    {
        $config = new Config("sqltest", "01", "test", "local");
        $underTest = new SQLCache($config);

        $mappingArray = array();
        $this->addToMappingArray($mappingArray, "jacket", "best jacket", null);
        $underTest->loadCache($mappingArray);

        $result = $underTest->get("jacket");
        $this->assertTrue($result != null);
        $this->assertEquals("best jacket", $result->masterQuery);

        $this->assertEquals("other", $underTest->get("other")->userQuery);
    }

    public function testOverwriteCache() : void
    {
        $config = new Config("sqltest", "02", "test", "local");
        $underTest = new SQLCache($config);
        
        $mappingArray = array();
        $this->addToMappingArray($mappingArray, "jacket", "best jacket", null);
        $this->addToMappingArray($mappingArray, "pants", "trousers", "./cat/trousers");
        $underTest->loadCache($mappingArray);

        $this->assertEquals("best jacket", $underTest->get("jacket")->masterQuery);
        $this->assertEquals("./cat/trousers", $underTest->get("pants")->redirect);

        // update with new cache
        $mappingArray = array();
        // no more jacket -> best jacket mapping
        $this->addToMappingArray($mappingArray, "pants", "trousers", null); // no redirect anymore
        $underTest->loadCache($mappingArray);

        $this->assertEquals("jacket", $underTest->get("jacket")->masterQuery);
        $this->assertEquals(null, $underTest->get("pants")->redirect);
    }

    private function addToMappingArray(array &$mappingArray, string $inputQuery, string $mappedQuery, ?string $redirect = null) : array 
    {   
        $mappingArray[$inputQuery] = array();
        $mappingArray[$inputQuery]["masterQuery"] = $mappedQuery;
        $mappingArray[$inputQuery]["redirect"] = $redirect;
        return $mappingArray;
    }

}