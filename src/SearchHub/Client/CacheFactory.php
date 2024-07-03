<?php

namespace SearchHub\Client;

use Exception;

class CacheFactory
{
    /**
     * @var Config
     */
    private Config $config;


    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return MappingCacheInterface
     */
    public function createCache()
    {
       try
       {
            //Try to connect to db
            //throw new Exception("DB did´t connected");
            $cache = new SQLCache($this->config);
       }
       catch(Exception $e)
       {
            //If not connected to DB - use local Cache
            $cache = new FileMappingCache($this->config);
        }
        //syslog(LOG_DEBUG, "Cache of type ".get_class($cache)." loaded. Last Modified Date = " . $cache->lastModifiedDate());
        return $cache;
    }
}