<?php

namespace SearchHub\Client;


use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Class SearchHubClient
 * @package SearchHub\Client
 */
class SearchHubClient {

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
     * @var MappingCache
     */
    protected $cache;


    public function setClientApiKey($clientApiKey): ?SearchHubClient
    {
        $this->clientApiKey = $clientApiKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientApiKey(): ?string
    {
        return $this->clientApiKey;
    }

    public function setAccountName($accountName): ?SearchHubClient
    {
        $this->accountName = $accountName;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getAccountName(): ?string
    {
        return $this->accountName;
    }


    public function setChannelName($channelName): ?SearchHubClient
    {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getChannelName(): ?string
    {
        return $this->channelName;
    }

    public function setStage($stage = null): ?SearchHubClient
    {
        $this->stage = ($stage === "qa") ? "qa" : "prod";
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStage(): ?string
    {
        return $this->stage;
    }

    protected function getHttpClient(): ClientInterface
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => (float) SearchHubConstants::REQUEST_TIMEOUT,
            ]);
        }
        return $this->httpClient;
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

    public function __construct(array $config)
    {
        if (isset($config['clientApiKey'])) {
            $this->setClientApiKey($config['clientApiKey']);
        }
        if (isset($config['accountName'])) {
            $this->setAccountName($config['accountName']);
        }
        if (isset($config['channelName'])) {
            $this->setChannelName($config['channelName']);
        }
        if (isset($config['stage'])) {
            $this->setStage($config['stage']);
        }

        $cacheFactory = new CacheFactory($config);
        $this->cache = $cacheFactory->createCache();

        //$this->cache->deleteCache(); //Delete local cache

        if ($this->cache->isEmpty() || $this->cache->age() > SearchHubConstants::MAPPING_CACHE_TTL ){
            $uri = SearchHubConstants::getMappingQueriesEndpoint($this->accountName, $this->channelName, $this->stage);
            try {
                $mappingsResponse = $this->getHttpClient()->get($uri, ['headers' => ['apikey' => SearchHubConstants::API_KEY]]);
                assert($mappingsResponse instanceof Response);
                $indexedMappings = $this->indexMappings(json_decode($mappingsResponse->getBody()->getContents(), true));
                $this->cache->loadCache($indexedMappings);
            } catch (Exception $e) {
                //TODO: log
            }
        }
    }

    public function mapQuery(string $query) : QueryMapping
    {
        return $this->cache->get($query);
    }

    /**
     * @throws Exception
     */
    public function optimize(string $query): QueryMapping
    {
        $startTimestamp = microtime(true);
        $mappedQuery= $this->mapQuery($query);

        $this->report(
            $query,
            $mappedQuery->masterQuery,
            microtime(true) - $startTimestamp,
            $mappedQuery->redirect,
        );
        return $mappedQuery;
    }

 /**
     * @param string $originalSearchString
     * @param string|null $optimizedSearchString
     * @param float $duration
     * @param string|null $redirect
     *
     * @return void
     * @throws Exception
     */
    protected function report(
        string $originalSearchString,
        string|null $optimizedSearchString,
        float $duration ,
        string|null $redirect,
    )  : void {
        $event = sprintf(
            '[
                    {
                        "from": "%s",
                        "to": "%s",
                        "redirect": %s,
                        "durationNs": %f,
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
            $optimizedSearchString = $optimizedSearchString == null ? $originalSearchString :  $optimizedSearchString,
            $redirect == null ? "null" : "\"$redirect\"",
            $duration * 1000 * 1000 * 1000 ,
            $this->accountName,
            $this->channelName,
        );

            echo $event;

            if ($optimizedSearchString){
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
}