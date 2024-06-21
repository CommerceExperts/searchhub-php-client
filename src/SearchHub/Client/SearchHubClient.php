<?php

namespace SearchHub\Client;



use GuzzleHttp\Exception\GuzzleException;


/**
 * Class SearchHubClient
 * @package SearchHub\Client
 */
class SearchHubClient {

    /*
     * @var LocalMapper|SaaSMapper
     */
    private  $mapper;

    public function __construct(Config $config)
    {
        if ($config->getType() === "SaaS") {
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
        $query = mb_strtolower($query);  // All letters must be small
        if (preg_match('/".*?"/', $query))//check "\"word\"""
        {
            return new QueryMapping($query, $query, null);
            //
        } else {
            return $this->mapper->mapQuery($query);
        }
    }
}