<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use SearchHub\Client\SearchHubConstants;

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
     * @param string|null $arg1
     * @param string|null $accountName
     * @param string|null $channelName
     * @return $this
     */

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

    public function __construct(string $arg1, string $accountName=null, string $channelName=null)
    {// Overloading of constructor
        if ($arg1 and !($accountName or $channelName)){
            $filename = $arg1;
            //$file = fopen("../../../Clients\\" . $filename, "r") or die("Unable to open file!");
            $file = fopen("C:\Users\Vitalii\Documents\GitHub\searchhub-php-client\Clients\\" . $filename, "r") or die("Unable to open file!");
            $data = fgets($file);
            fclose($file);

            $parts = explode(",", $data);

// Видаляємо пробіли з кожної частини
            $clientApiKey = trim($parts[0]);
            $accountName = trim($parts[1]);
            $channelName = trim($parts[2]);

            $this->setClientApiKey($clientApiKey);
            $this->setAccountName($accountName);
            $this->setChannelName($channelName);
        }
        else {
            // 2+ argument - parameters of client
            $clientApiKey = $arg1;
            $this->setClientApiKey($clientApiKey);
            $this->setAccountName($accountName);
            $this->setChannelName($channelName);
        }
    }

    public function __toString()
    {
        return "clientApiKey: " . $this->getClientApiKey() . " | accountName: " . $this->getAccountName() . " | channelName: ". $this->channelName . "\n";
    }

    public function optimize(SearchHubRequest $searchHubRequest): SearchHubRequest
    {
        $startTimestamp = microtime(true);
        $mappings = $this->loadMappings(SearchHubConstants::getMappingQueriesEndpoint(SearchHubConstants::ACCOUNT_NAME, SearchHubConstants::CHANNEL_NAME));
        if (isset($mappings[$searchHubRequest->getUserQuery()]) ) {
            $mapping = $mappings[$searchHubRequest->getUserQuery()];
            if (isset($mapping["redirect"])) {
                if (strpos($mapping["redirect"], 'http') === 0) {
                    //TODO: log
                    header('Location: ' . $mapping["redirect"]);
                }
                else {
                    //TODO: log
                    header('Location: ' . SearchHubConstants::REDIRECTS_BASE_URL . $mapping["redirect"]);
                }
//                $this->report(
//                    $searchHubRequest->getUserQuery(),
//                    $mapping["masterQuery"],
//                    microtime(true) - $startTimestamp,
//                    true
//                );
                exit;
            }
            else {
                //TODO: log
                $searchHubRequest->setSearchQuery($mapping["masterQuery"]);
//                $this->report(
//                    $searchHubRequest->getUserQuery(),
//                    $mapping["masterQuery"],
//                    microtime(true) - $startTimestamp,
//                    false
//                );
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
        $cache = SearchHubConstants::getMappingCache(SearchHubConstants::ACCOUNT_NAME, SearchHubConstants::CHANNEL_NAME);
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
                $lastModifiedResponse = $this->getHttpClient()->get(SearchHubConstants::getMappingLastModifiedEndpoint(SearchHubConstants::ACCOUNT_NAME, SearchHubConstants::CHANNEL_NAME), ['headers' => ['apikey' => SearchHubConstants::API_KEY]]);
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
            $SearchHubConstants::ACCOUNT_NAME,
            $SearchHubConstants::CHANNEL_NAME
        );

        $promise = $this->getHttpClient((float) 0.01)->requestAsync(
            'post',
            $SearchHubConstants::MAPPINGSTATS_ENDPOINT,
            [
                'headers' => [
                    'apikey' => $SearchHubConstants::API_KEY,
                    'X-Consumer-Username' => $SearchHubConstants::ACCOUNT_NAME,
                    'Content-type' => 'application/json',
                ],
                'body' => $event,
            ]
        );
        try {
            $promise->wait();
        } catch (\Exception $ex) {
             /*
              * will throw a timeout exception which we ignore, as we don't want to wait for any result
              */
        }
    }
}
