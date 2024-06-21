<?php

namespace SearchHub\Client;

use App\MappingCacheMock;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

class LocalMapper implements SearchHubMapperInterface
{
    /**
     * @var FileMappingCache|SQLCache|MappingCacheMock
     */
    private  $mappingCache;

    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;

    /**
     * @var Config
     */
    private Config $config;

    public function __construct(Config $config, MappingCacheInterface $cache=null)
    {
        $this->config = $config;

        if ($cache === null){
            $cacheFactory = new CacheFactory($config);
            $cache = $cacheFactory->createCache();
        }

        $this->mappingCache = $cache;
        if ($this->mappingCache->isEmpty() || ($this->getSaaSLastModifiedDate() > $this->mappingCache->lastModifiedDate())) {
            $update = new MappingDataUpdate();
            $update->updateMappingData($config, $this->mappingCache, $this->getHttpClient());
        }
    }

    public function getSaaSLastModifiedDate(): int
    {
        if ($this->config->getStage() === "qa")
        {
            $uri = "https://qa-api.searchhub.io/modificationTime?tenant={$this->config->getAccountName()}.{$this->config->getChannelName()}";
        }
        else
        {
            $uri = "https://api.searchhub.io/modificationTime?tenant={$this->config->getAccountName()}.{$this->config->getChannelName()}";
        }

        $response = $this->getHttpClient()->get($uri, ['headers' => ['apikey' => API_KEY::API_KEY]]);
        assert($response instanceof Response);

        return (int)json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @throws Exception
     */
    public function mapQuery($userQuery): QueryMapping
    {
        $startTimestamp = microtime(true);
        $mappedQuery = $this->mappingCache->get($userQuery);
        $this->report(
            $userQuery,
            $mappedQuery->masterQuery,
            microtime(true) - $startTimestamp,
            $mappedQuery->redirect,
        );
        return $mappedQuery;
    }

    protected function getHttpClient($timeOut = null): ClientInterface
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => (float)$timeOut ? $timeOut : SearchHubConstants::REQUEST_TIMEOUT,]);
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
        ?string $optimizedSearchString,
        float       $duration,
        ?string $redirect
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
            $this->config->getAccountName(),
            $this->config->getChannelName(),
        );

        if ($optimizedSearchString) {
            $promise = $this->getHttpClient((float)0.3)->requestAsync('post', SearchHubConstants::getMappingDataStatsEndpoint($this->config->getStage()),
                [
                    'headers' => [
                        'apikey' => $this->config->getClientApiKey(),
                        'Content-Type' => 'application/json',
                    ],
                    'body' => $event,
                ]
            );
            try {
                $promise->wait();
            } catch (Exception $e) {
//                $errorMessage = $e->getMessage();
//                $errorCode = $e->getCode();
//                $file = $e->getFile();
//                $line = $e->getLine();
//                error_log("$originalSearchString Error: $errorMessage (Code: $errorCode) in $file on line $line");
            }
        }
    }
}