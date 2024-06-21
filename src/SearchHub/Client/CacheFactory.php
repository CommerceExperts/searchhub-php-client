<?php

namespace SearchHub\Client;

use Exception;

class CacheFactory
{
    /**
     * @var Config
     */
    private Config $config;


    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @return SQLCache|FileMappingCache
     */
    public function createCache()
    {
        try
        {
            //Try to connect to db
            //throw new Exception("DB did´t connected");
            return new SQLCache($this->config);
        }
        catch(\Exception $e)
        {
            //If not connected to DB - use local Cache
            return new FileMappingCache($this->config);
        }
    }
}