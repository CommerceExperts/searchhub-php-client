<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;

class SaaSMapper implements SearchHubMapperInterface
{
    private ?string $url;

    private Config $config;

    /**
     * @var ClientInterface
     */
    protected ClientInterface $httpClient;

    public function __construct(Config $config, $url=null)
    {
        $this->url = null;
        if ($url !== null){
            $this->url = $url;
        }

        $this->config = $config;
        //$this->url = $url;
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function mapQuery($userQuery): QueryMapping
    {
        if ($this->url === null)
        {
            $this->url = SearchHubConstants::getSaaSEndpoint($this->config->getStage(), $this->config->getAccountName(), $this->config->getChannelName(), $userQuery);
        }
        else
        {
            $this->url = $this->url . $userQuery;
        }

        $response = $this->getHttpClient()->get($this->url, ['headers' => ['apikey' => $this->config->getClientApiKey()]]);
        assert($response instanceof Response);
        $mappedQuery = json_decode($response->getBody()->getContents(), true);

        return new QueryMapping($userQuery, $mappedQuery["masterQuery"] ?: $userQuery, $mappedQuery["redirect"] ?: null);
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