<?php

namespace SearchHub\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;

class SaaSMapper implements SearchHubMapperInterface
{
    private Config $config;

    /**
     * @var ?ClientInterface
     */
    protected ?ClientInterface $httpClient=null;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function mapQuery($userQuery): QueryMapping
    {
        $url = $this->config->getSaaSEndPoint($userQuery);

        $response = $this->getHttpClient()->get($url, ['headers' => ['apikey' => $this->config->getClientApiKey()]]);
        assert($response instanceof Response);
        $mappedQuery = json_decode($response->getBody()->getContents(), true);

        return new QueryMapping($userQuery, $mappedQuery ? $mappedQuery["masterQuery"] : $userQuery, $mappedQuery ? $mappedQuery["redirect"] : null);
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