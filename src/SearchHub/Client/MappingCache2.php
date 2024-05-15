<?php

namespace SearchHub\Client;

use http\QueryString;
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
    //protected $folderPath;

    /**
     * @var string|null
     */
    protected $key;

    /**
     * Searching locale cache
     */
    public function __construct(string $accountName, string $channelName){
        //$this->folderPath = "/tmp/cache/data/cache/searchhub/{$accountName}/{$channelName}";

        $this->cache = SearchHubConstants::getMappingCache($accountName, $channelName);
        $this->setKey($this->cache->generateKey($accountName, $channelName));

        //$this->cache = $this->getCache($this->key);
    }



    public function setKey($key): MappingCache2
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return MappingCache2|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    public function searchQuery(array $mappings, string $query){
        foreach($mappings as $neededQuery => $secondArray){
            if ($neededQuery === $query)
            {
                return $secondArray["masterQuery"];
            }
        }
        return null;
    }

    public function get(string $query): ?string
    {
        $mappings = $this->getCache($this->key);
        return $this->searchQuery($mappings, $query);
    }

    public function getCache(string $cacheFile)
    {
        if (file_exists($cacheFile) ) {
            //if (time() - filemtime($cacheFile) < SearchHubConstants::MAPPING_CACHE_TTL) {
                return json_decode(file_get_contents($cacheFile), true);
            //}
        }
        return null;
    }

    public function deleteCache(): void
    {
        if (file_exists($this->key)) {
            unlink($this->key);
        }

    }

    public function loadCache(string $loadedCache): void
    {
        $this->cache->write($this->key, $loadedCache);
    }



    public function isEmpty(): bool
    {
        return $this->cache->getTimestamp($this->key) === 0;
    }

}