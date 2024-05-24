<?php

namespace SearchHub\Client;



use GuzzleHttp\Exception\GuzzleException;


/**
 * Class SearchHubClient
 * @package SearchHub\Client
 */
class SearchHubClient {

    private LocalMapper|SaaSMapper $mapper;

    public function __construct(array $config)
    {
        if ($config['type'] === "SaaS") {
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
        echo $query . "\n";
        if (preg_match('/".*?"/', $query))//check "\"word\"""
        {
            echo "skip: $query\n";
            return new QueryMapping($query, $query, null);
            //
        } else {
            $query = mb_strtolower($query);  // Important all letters make small
            return $this->mapper->mapQuery($query);
        }
    }
}