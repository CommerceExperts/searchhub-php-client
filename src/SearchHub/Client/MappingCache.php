<?php

namespace SearchHub\Client;

class MappingCache implements MappingCacheInterface
{
    protected $cache; //TODO Datatyp

    /**
     * Get cache and save it
     *
     * @param $cache //TODO Datatyp
     *
     */
    public function __construct(string $accountName, string $channelName){
        $this->cache = SearchHubConstants::getMappingCache($accountName, $channelName);
        // TODO: load mappings from file
    }


    /**
     * Get Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param string $query
     *
     * @return SearchHubRequest
     */
    public function get(string $query): string{
        return "Wir arbeiten davon";
    }

    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param void
     *
     * @return void
     */
    public function deleteCache(): void{
        $this->cache = null;
    }

    /**
     * Rewrite cache
     *
     * @param void//TODO
     *
     * @return void
     */
    public function loadCache(array $loadedCache): void{
        $this->cache = $loadedCache;
    }

    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param void
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return boolval($this->cache);
    }
}