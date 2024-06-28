<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class LocalMapper implements SearchHubMapperInterface
{
    /**
     * @var MappingCacheInterface
     */
    private  $mappingCache;

    /**
     * @var ?Client
     */
    private ?Client $httpClient = null;

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
        if ($this->mappingCache->lastModifiedDate() + $this->config->getMappingCacheTTL() <= time()){
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

        $response = $this->getHttpClient()->get($uri, ['headers' => ['apikey' => $this->config->getClientApiKey()]]);
        assert($response instanceof Response);

        return (int)json_decode($response->getBody()->getContents(), true) / 1000;
    }

    /**
     * @throws Exception
     */
    public function mapQuery(string $userQuery): QueryMapping
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

    protected function getHttpClient(?float $timeOut = null): Client
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => (float)($timeOut ?? $this->config->getMappingDataUpdateTimeout())
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
        ?string $optimizedSearchString,
        float       $duration,
        ?string $redirect
    ): void
    {
        $event = sprintf(
            '[
                    {
                        "from": %s,
                        "to": %s,
                        "redirect": %s,
                        "durationNs": %d,
                        "timestampMillis" : %d,
                        "tenant": {
                            "name": "%s",
                            "channel": "%s"
                        },
                        "queryMapperType": "SimpleQueryMapper",
                        "statsType": "mappingStats",
                        "libVersion": "php-client 2/php-version ' . phpversion() . '"
                    }
                ]',
            json_encode($originalSearchString),
            json_encode($optimizedSearchString == null ? $originalSearchString : $optimizedSearchString),
            $redirect == null ? "null" : json_encode("$redirect"),
            $duration * 1000 * 1000 * 1000,
            time() * 1000,
            $this->config->getAccountName(),
            $this->config->getChannelName(),
        );


        if ($optimizedSearchString) {
            $url = $this->config->getMappingDataStatsEndpoint();
            $promise = $this->getHttpClient($this->config->getReportTimeout())->requestAsync('post', $url,
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
                $errorMessage = $e->getMessage();
                $errorCode = $e->getCode();
                $file = $e->getFile();
                $line = $e->getLine();
                error_log("Sending searchHub mapping-stats failed for query '$originalSearchString'. Consider increasing report_timeout in config. Error: $errorMessage (Code: $errorCode) in $file on line $line");
            }
        }
    }
}