<?php

namespace SearchHub\Client;

use Twig\Cache\FilesystemCache;

class MappingCache implements MappingCacheInterface
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

        // TODO: if cache is too old (> MAX_TTL), then delete


        //$this->cache = $this->getCache($this->key);
    }

    private function setKey($key): MappingCache
    {
        $this->key = $key;
        return $this;
    }

    public function searchQuery(array $mappings, string $query){
        return array_key_exists($query, $mappings) ? $mappings[$query]["masterQuery"] : null;
    }

    public function get(string $query): ?string
    {
        $mappings = $this->getCache($this->key);
        return $this->searchQuery($mappings, $query);
    }

    private function getCache(string $cacheFile)
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

    public function loadCache(array $loadedCache): void
    {
        $this->cache->write($this->key, json_encode($loadedCache));
    }



    public function isEmpty(): bool
    {
        return $this->cache->getTimestamp($this->key) === 0;
    }

    public function age(): int {
        if (file_exists($this->key)) {
            return time() -$this->cache->getTimestamp($this->key);
        }
        return 0;
    }

}