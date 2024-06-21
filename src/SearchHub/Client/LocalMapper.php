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
     * @var ?ClientInterface
     */
    private ?ClientInterface $httpClient = null;

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
        if ($this->updateRequire()) {
                $update = new MappingDataUpdate();
                $update->updateMappingData($config, $this->mappingCache, $this->getHttpClient());
            }
    }

    private function updateRequire(): bool
    {
        if ($this->mappingCache->isEmpty()){
            return True;
        }
        if ($this->mappingCache->lastModifiedDate() + SearchHubConstants::MAPPING_CACHE_TTL <= time()){
            if ($this->getSaaSLastModifiedDate() > $this->mappingCache->lastModifiedDate()){
                return True;
            }
            else
            {
               $this->mappingCache->resetAge();
            }
        }

        return False;
    }

    public function getSaaSLastModifiedDate(): int
    {
        $uri = $this->config->getSaaSLastModifiedDateEndpoint();

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
            $url = $this->config->getMappingDataStatsEndpoint();
            $promise = $this->getHttpClient(0.3)->requestAsync('post', $url,
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