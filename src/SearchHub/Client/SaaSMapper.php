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

    protected ?string $url;

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

    public function __construct(array $config, $url=null)
    {
        $this->url = null;
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
        if ($url !== null){
            $this->url = $url;
        }
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function mapQuery($userQuery): QueryMapping
    {
        if ($this->url === null){
            $this->url = SearchHubConstants::getSaaSEndpoint($this->stage, $this->accountName, $this->channelName, $userQuery);//$this->baseUrl . "{$urlQuery}";
        } else {
            $this->url = $this->url . $userQuery;
        }


        $response = $this->getHttpClient()->get($this->url, ['headers' => ['apikey' => SearchHubConstants::API_KEY]]);
        assert($response instanceof Response);
        $mappedQuery = json_decode($response->getBody()->getContents(), true);

        return new QueryMapping($userQuery, $mappedQuery["masterQuery"] ?: $userQuery,
            $mappedQuery["redirect"] ?: null);
    }

    protected function getHttpClient($timeOut = null): ClientInterface
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => (float)$timeOut ? $timeOut : SearchHubConstants::REQUEST_TIMEOUT,]);
        }
        return $this->httpClient;
    }
}