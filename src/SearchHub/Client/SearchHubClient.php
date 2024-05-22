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
class SearchHubClient {

    private LocalMapper $mapper;

    public function __construct(array $config)
    {
        if ($config['stage'] === "saas") {
            // $this->mapper = new SaasMapper($config);
        }
        else
        {
            $this->mapper = new LocalMapper($config);
        }

    }

    public function mapQuery(string $query) : QueryMapping
    {
        return $this->mapper->mapQuery($query);
    }


}