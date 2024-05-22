<?php

namespace SearchHub\Client;


use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Class SearchHubClient
 * @package SearchHub\Client
 */
class SearchHubClient {

    private LocalMapper|SaaSMapper $mapper;

    public function __construct(array $config)
    {
        if ($config['type'] === "saas") {
            $this->mapper = new SaaSMapper($config);
        }
        else
        {
            $this->mapper = new LocalMapper($config);
        }

    }

    /**
     * @throws GuzzleException
     */
    public function mapQuery(string $query) : QueryMapping
    {
        $query = mb_strtolower($query);
        return $this->mapper->mapQuery($query);
    }


}