<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

class LocalMapper implements SearchHubMapperInterface
{
    protected FileMappingCache|SQLCache $mappingCache;

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

    public function setClientApiKey($clientApiKey): LocalMapper
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

    public function setAccountName($accountName): LocalMapper
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


    public function setChannelName($channelName): LocalMapper
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

    public function setStage($stage = null): LocalMapper
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
        $this->mappingCache = $cacheFactory->createCache();

        if ($this->mappingCache->isEmpty() || $this->mappingCache->age() > SearchHubConstants::MAPPING_CACHE_TTL) {
            $uri = SearchHubConstants::getMappingQueriesEndpoint($this->accountName, $this->channelName, $this->stage);
            try {
                $mappingsResponse = $this->getHttpClient()->get($uri, ['headers' => ['apikey' => SearchHubConstants::API_KEY]]);
                assert($mappingsResponse instanceof Response);
                $indexedMappings = $this->indexMappings(json_decode($mappingsResponse->getBody()->getContents(), true));
                $this->mappingCache->loadCache($indexedMappings);
            } catch (Exception $e) {
                //TODO: log
            }
        }

    }

    public function mapQuery($userQuery): QueryMapping
    {
        $startTimestamp = microtime(true);
        $mappedQuery = $this->mappingCache->get($userQuery);;

        $this->report(
            $userQuery,
            $mappedQuery->masterQuery,
            microtime(true) - $startTimestamp,
            $mappedQuery->redirect,
        );
        return $mappedQuery;

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

    protected function getHttpClient($timeOut = null): ClientInterface
    {

        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => (float)$timeOut ? $timeOut : SearchHubConstants::REQUEST_TIMEOUT,
            ]);
        }
        return $this->httpClient;
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
        string      $originalSearchString,
        string|null $optimizedSearchString,
        float       $duration,
        string|null $redirect,
    ): void
    {
        $event = sprintf(
            '[
                    {
                        "from": "%s",
                        "to": "%s",
                        "redirect": %s,
                        "durationNs": %d,
                        "timestampMillis" : %d,
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
            $optimizedSearchString == null ? $originalSearchString : $optimizedSearchString,
            $redirect == null ? "null" : "\"$redirect\"",
            $duration * 1000 * 1000 * 1000,
            time() * 1000,
            $this->accountName,
            $this->channelName,
        );

        echo $event;

        if ($optimizedSearchString) {

            $promise = $this->getHttpClient((float)0.01)->requestAsync(
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


            try {
                $promise->wait();
            } catch (Exception $e) {
                //TODO log
            }
        }
    }
}