<?php

namespace SearchHub\Client;

class MappingCache implements MappingCacheInterface
{
    protected $cache; //TODO Datatyp

    /**
     * Get cache and save it
     *
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
     *
     */
    public function get(string $query): string
    {
        $mappings = $this->cache;   //$this->loadMappings(SearchHubConstants::getMappingQueriesEndpoint($this->accountName, $this->channelName, $this->stage));
        if (isset($mappings[$query])) {
            $mapping = $mappings[$query];
            return $mapping["masterQuery"];
        }
        return "";
    }

    public function deleteCache(): void{
        $this->cache = null;
    }

    public function loadCache(array $loadedCache): void{
        $this->cache = $loadedCache;
    }

    public function getCache(): \Twig\Cache\FilesystemCache
    {
        return $this->cache;
    }

    public function isEmpty(): bool
    {
        return boolval($this->cache);
    }
}