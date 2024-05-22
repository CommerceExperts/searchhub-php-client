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
class ClientDB {

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
    protected $db;


    public function setClientApiKey($clientApiKey): ?ClientDB
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

    public function setAccountName($accountName): ?ClientDB
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


    public function setChannelName($channelName): ?ClientDB
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

    public function setStage($stage = null): ?ClientDB
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

        $this->db = new DB();

        //$this->db->deleteCache(); //Delete local DB

        if ($this->db->isEmpty() || $this->db->age() > SearchHubConstants::MAPPING_CACHE_TTL){
            $startTime = microtime(true); //TODO Remove
            $uri = SearchHubConstants::getMappingQueriesEndpoint($this->accountName, $this->channelName, $this->stage);
            try {
                $mappingsResponse = $this->getHttpClient()->get($uri, ['headers' => ['apikey' => SearchHubConstants::API_KEY]]);
                assert($mappingsResponse instanceof Response);
                $indexedMappings = $this->indexMappings(json_decode($mappingsResponse->getBody()->getContents(), true));
                $this->db->loadCache($indexedMappings);
            } catch (Exception $e) {
                //TODO: log
            }

            $execution_time = (microtime(true) - $startTime) * 1000; //TODO Remove

            //echo("!!!!!Data inserting time: " . $execution_time."ms!!!!!\n"); //TODO Remove
        }
    }

    public function mapQuery(string $query) : QueryMapping
    {
        return $this->db->get($query);
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
            $mappedQuery->redirect

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
        string|null $redirect
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
                        "queryMapperType": "SQLiteMapper",
                        "statsType": "mappingStats",
                        "libVersion": "php-client 1.0"
                    }
                ]',
            $originalSearchString,
            $optimizedSearchString = $optimizedSearchString == null ? $originalSearchString :  $optimizedSearchString,
            $redirect == null ? "null" : "\"$redirect\"",
            $duration * 1000 * 1000 * 1000 ,
            $this->accountName,
            $this->channelName
        );

            //echo $event;

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