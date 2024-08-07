<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;


class MappingDataUpdate
{
    function updateMappingData(Config $config, MappingCacheInterface $cache, Client $httpClient): void
    { //should have been called every 10-minute
        try {
            $uri = $config->getMappingQueriesEndpoint();
            $mappingsResponse = $httpClient->get($uri, ['headers' => ['apikey' => $config->getClientApiKey()]]);
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

    /**
     * @throws Exception
     */
    protected function indexMappings(?array $mappingsRaw): array
    {
        if ($mappingsRaw === null)
        {
            throw new Exception("Failed to retrieve data from the server.");
        }

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