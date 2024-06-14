<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class MappingDataUpdate
{
    function updateMappingData($config, $cache=null, $httpClient=null): void
    { //should have been called every 10-minute

        if ($cache === null){
            $cacheFactory = new CacheFactory($config);
            $cache = $cacheFactory->createCache();
        }

        if ($httpClient === null) {
            $httpClient = new Client(['timeout' => SearchHubConstants::REQUEST_TIMEOUT]);
        }

        try {
            $uri = SearchHubConstants::getMappingQueriesEndpoint($config["accountName"], $config["channelName"], $config["stage"]);
            $mappingsResponse = $httpClient->get($uri, ['headers' => ['apikey' => API_KEY::API_KEY]]);
            assert($mappingsResponse instanceof Response);
            $indexedMappings = $this->indexMappings(json_decode($mappingsResponse->getBody()->getContents(), true));
            $cache->loadCache($indexedMappings);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $file = $e->getFile();
            $line = $e->getLine();
            error_log("Error while fetching mapping data: $errorMessage (Code: $errorCode) in $file on line $line");
        }
    }

    protected function indexMappings($mappingsRaw): array
    {
        $indexedMappings = array();
        if (isset($mappingsRaw["clusters"]) && is_array($mappingsRaw["clusters"])) { //v2
            foreach ($mappingsRaw["clusters"] as $mapping) {
                foreach ($mapping["queries"] as $variant) {
                    $indexedMappings[$variant] = array();
                    $indexedMappings[$variant]["masterQuery"] = $mapping["masterQuery"];
                    $indexedMappings[$variant]["redirect"] = $mapping["redirect"];
                }
            }
        }
        return $indexedMappings;
    }
}