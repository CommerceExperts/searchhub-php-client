<?php

namespace SearchHub\Client;

use Twig\Cache\FilesystemCache;

class MappingCache2 implements MappingCacheInterface
{
    /**
     * @var FilesystemCache|null
     */
    protected $cache;

    /**
     * @var string
     */
    protected $folderPath;

    /**
     * @var string|null
     */
    protected $key;

    /**
     * Searching locale cache
     */
    public function __construct(string $accountName, string $channelName){
        $this->folderPath = "/tmp/cache/data/cache/searchhub/{$accountName}/{$channelName}";

        $this->cache = SearchHubConstants::getMappingCache($accountName, $channelName);
        $this->setKey($this->cache->generateKey($accountName, $channelName));

        $this->cache = $this->getCache($this->key);
    }

    public function setKey($key): ?SearchHubClient2
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return MappingCache|null
     */
    public function getKey(): ?MappingCache
    {
        return $this->key;
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
    { // TODO Searching query in cache
//        $this->cache->generateKey($query);   //$this->loadMappings(SearchHubConstants::getMappingQueriesEndpoint($this->accountName, $this->channelName, $this->stage));
//        if (isset($mappings[$query])) {
//            $mapping = $mappings[$query];
//            return $mapping["masterQuery"];
//        }
        return "";
    }

    public function getCache(string $cacheFile)
    {
        if (file_exists($cacheFile) ) {
            if (time() - filemtime($cacheFile) < SearchHubConstants::MAPPING_CACHE_TTL) {
                //return file_get_contents($cacheFile);
                return json_decode(file_get_contents($cacheFile), true);
            }
        }
        return null;
    }

    public function deleteCache(): void
    {
        $this->cache->write($this->key, null);
        $this->cache = null;
    }

    public function loadCache(array $loadedCache): void
    {
        $this->cache->write($this->key, $loadedCache);
    }



    public function isEmpty(): bool
    {
        return $this->cache->getTimestamp($this->key) === 0;
    }

}