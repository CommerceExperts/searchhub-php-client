<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;

class SaaSMapper implements SearchHubMapperInterface
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

    public function setClientApiKey($clientApiKey): SaaSMapper
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

    public function setAccountName($accountName): SaaSMapper
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

    public function setChannelName($channelName): SaaSMapper
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

    public function setStage($stage = null): SaaSMapper
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
    }


    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function mapQuery($userQuery): QueryMapping
    {
        $userQuery = mb_strtolower($userQuery);
        $startTimestamp = microtime(true);
        $urlQuery = rawurlencode($userQuery);

        $url = "https://{$this->stage}-saas.searchhub.io/smartquery/v2/{$this->accountName}/{$this->channelName}?userQuery={$urlQuery}";


        $response = $this->getHttpClient()->get($url, ['headers' => ['apikey' => SearchHubConstants::API_KEY]]);
        assert($response instanceof Response);
        $mappedQueryJSON = $response->getBody()->getContents();


        $mappedQuery = json_decode($mappedQueryJSON, true);

        $this->report(
            $userQuery,
            $mappedQuery["masterQuery"],
            microtime(true) - $startTimestamp,
            $mappedQuery["redirect"],
        );
        return new QueryMapping($userQuery, $mappedQuery["masterQuery"], $mappedQuery["redirect"]);
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