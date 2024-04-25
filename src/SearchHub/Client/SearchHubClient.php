<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;


/**
 * Class SearchHubClient
 * @package SearchHub\Client
 */
class SearchHubClient implements SearchHubClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var string|null
     */
    protected $clientApiKey;

    /**
     * @var string|null
     */
    protected $accountName;

    /**
     * @var string|null
     */
    protected $channelName;

    /**
     * @var string
     */
    protected $stage;

    /**
     * @var MappingCacheInterface
     */
    protected $mappingCache;

    public function setClientApiKey($apiKey): ?SearchHubClient
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientApiKey(): ?string
    {
        return $this->clientApiKey;
    }

    public function setAccountName($accountName): ?SearchHubClient {
        $this->accountName = $accountName;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getAccountName(): ?string {
        return $this->accountName;
    }


    public function setChannelName($channelName): ?SearchHubClient {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getChannelName(): ?string {
        return $this->channelName;
    }

    public function setStage ($stage=null): ?SearchHubClient {
        $this->stage = ($stage === "qa") ? "qa" : "prod";
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStage(): ?string {
        return $this->stage;
    }

    /**
     * @param string $arg1
     * @param string|null $accountName
     * @param string|null $channelName
     * @param string|null $stage
     * @return $this
     */

    public function __construct(string $arg1, string $accountName=null, string $channelName=null, string $stage=null)
    {// Overloading of constructor
        if ($arg1 and !($accountName or $channelName or $stage)){
            $jsonPath = $arg1;

            $jsonString = file_get_contents($jsonPath);

            $data = json_decode($jsonString, true);
            if (isset($data['apiKey'])) {
                $this->setClientApiKey($data['apiKey']);
            }
            if (isset($data['accountName'])) {
                $this->setAccountName($data['accountName']);
            }
            if (isset($data['channelName'])) {
                $this->setChannelName($data['channelName']);
            }
            if (isset($data['stage'])) {
                $this->setStage($data['stage']);
            }
            // TODO: $this->mappingCache = new MappingCache(...)
//             if ($this->mappingCache->isEmpty()) {
//                $mappings = $this->loadMappings(SearchHubConstants::getMappingQueriesEndpoint($this->accountName, $this->channelName, $this->stage));
//                $this->mappingCache->loadCache($mappings);
//             }
        }
        else {
            // 2+ argument - parameters of client
            $clientApiKey = $arg1;
            $this->setClientApiKey($clientApiKey);
            $this->setAccountName($accountName);
            $this->setChannelName($channelName);
            $this->setStage($stage);
        }
        return $this;
    }

    public function __toString()
    {
        return "clientApiKey: " . $this->getClientApiKey() . " | accountName: " . $this->getAccountName() . " | channelName: ". $this->channelName ." | stage: $this->stage\n";
    }

    /**
     * @throws Exception
     */
    public function optimize(SearchHubRequest $searchHubRequest): SearchHubRequest
    {
        $startTimestamp = microtime(true);
        //$mapping = $this->mappingCache->get($searchHubRequest->getUserQuery());
        $mappings = $this->loadMappings(SearchHubConstants::getMappingQueriesEndpoint($this->accountName, $this->channelName, $this->stage));
        if (isset($mappings[$searchHubRequest->getUserQuery()]) ) {
            $mapping = $mappings[$searchHubRequest->getUserQuery()];

        //if ($mapping != null ) {
            if (isset($mapping["redirect"])) {
                if (strpos($mapping["redirect"], 'http') === 0) {
                    //TODO: log
                    header('Location: ' . $mapping["redirect"]);
                }
                else {
                    //TODO: log
                    header('Location: ' . SearchHubConstants::REDIRECTS_BASE_URL . $mapping["redirect"]);
                }
                $this->report(
                    $searchHubRequest->getUserQuery(),
                    $mapping["masterQuery"],
                    microtime(true) - $startTimestamp,
                    true
                );
                exit;
            }
            else {
                //TODO: log
                $searchHubRequest->setSearchQuery($mapping["masterQuery"]);
                $this->report(
                    $searchHubRequest->getUserQuery(),
                    $mapping["masterQuery"],
                    microtime(true) - $startTimestamp,
                    false
                );
            }
            return $searchHubRequest;
        }
        return $searchHubRequest;

    }

    /**
     * Get Http Client
     *
     * @throws Exception
     *
     * @return ClientInterface
     */
    protected function getHttpClient(): ClientInterface
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => (float) SearchHubConstants::REQUEST_TIMEOUT,
            ]);
        }
        return $this->httpClient;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function loadMappings(string $uri): array
    {
        $cache = SearchHubConstants::getMappingCache($this->accountName, $this->channelName);
        $key = $cache->generateKey("SearchHubClient", $uri);

        $mappings = $this->loadMappingsFromCache($key);
        if ($mappings === null ) {
            try {
                $mappingsResponse = $this->getHttpClient()->get($uri, ['headers' => ['apikey' => SearchHubConstants::API_KEY]]);
                assert($mappingsResponse instanceof Response);
                $indexedMappings = $this->indexMappings(json_decode($mappingsResponse->getBody()->getContents(), true));
                $cache->write($key, json_encode($indexedMappings));
                return $indexedMappings;
            } catch (Exception $e) {
                //TODO: log
                return array();
            }
        }
        return json_decode($mappings, true);
    }

    protected function loadMappingsFromCache(string $cacheFile)
    {
        if (file_exists($cacheFile) ) {
            if (time() - filemtime($cacheFile) < SearchHubConstants::MAPPING_CACHE_TTL) {
                return file_get_contents($cacheFile);
            } else {
                $lastModifiedResponse = $this->getHttpClient()->get(SearchHubConstants::getMappingLastModifiedEndpoint($this->accountName, $this->channelName, $this->stage), ['headers' => ['apikey' => SearchHubConstants::API_KEY]]);
                assert($lastModifiedResponse instanceof Response);
                if (filemtime($cacheFile) > ((int)($lastModifiedResponse->getBody()->getContents()) / 1000 + SearchHubConstants::MAPPING_CACHE_TTL)) {
                    touch($cacheFile);
                    return file_get_contents($cacheFile);
                }
            }
        }
        return null;
    }

    /**
     * @param $mappingsRaw
     * @return array
     */
    protected function indexMappings($mappingsRaw): array
    {
        $indexedMappings = array();
        if (isset($mappingsRaw["clusters"]) && is_array($mappingsRaw["clusters"])) { //v2
            foreach ($mappingsRaw["clusters"] as $mapping) {
                foreach ($mapping["queries"] as $variant) {
                    $indexedMappings[$variant] = array();
                    $indexedMappings[$variant]["masterQuery"] = $mapping["masterQuery"];
                    if ($mapping["redirect"] !== null) {
                        $indexedMappings[$variant]["redirect"] = $mapping["redirect"];
                    }
                }
            }
        }
        return $indexedMappings;
    }


    /**
     * @param string $originalSearchString
     * @param string $optimizedSearchString
     * @param float $duration
     * @param bool $redirect
     *
     * @return void
     * @throws Exception
     */
    protected function report(
            string $originalSearchString,
            string $optimizedSearchString,
            float $duration,
            bool $redirect
        ): void {
        $event = sprintf(
            '[
                {
                    "from": "%s",
                    "to": "%s",
                    "redirect": "%s",
                    "durationNs": %d,
                    "tenant": {
                        "name": "%s",
                        "channel": "%s"
                    },
                    "queryMapperType": "SimpleQueryMapper",
                    "statsType": "mappingStats",
                    "libVersion": "php-client 1.0"
                }
            ]',
            $originalSearchString,
            $optimizedSearchString,
            $redirect,
            $duration * 1000 * 1000 * 1000,
            $this->accountName,
            $this->channelName
        );

        echo $event;

        $this->getHttpClient()->requestAsync(
            'post',
            SearchHubConstants::getMappingDataStatsEndpoint($this->stage),
            [
                'headers' => [
                    'apikey' => $this->clientApiKey,
                    'Content-Type' => 'application/json',
                ],
                'body' => $event,
            ]
        );

    }
}
