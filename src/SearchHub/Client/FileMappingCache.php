<?php

namespace SearchHub\Client;

use Twig\Cache\FilesystemCache;

class FileMappingCache implements MappingCacheInterface
{
    /**
     * @var FilesystemCache|null
     */
    protected ?FilesystemCache $cache;

    /**
     * @var string|null
     */
    protected ?string $key;

    /**
     * Searching locale cache
     */
    public function __construct(string $accountName, string $channelName, string $stage){
        $this->cache = new FilesystemCache("/tmp/cache/data/cache/searchhub/{$accountName}/{$channelName}/{$stage}");
        $this->setKey($this->cache->generateKey($accountName, $channelName));
    }

    private function setKey($key): void
    {
        $this->key = $key;
    }


    public function get(string $query): QueryMapping
    {
        $query = mb_strtolower($query);
        $mappings = $this->getCache();
        $masterQuery = array_key_exists($query, $mappings) ? $mappings[$query]["masterQuery"] : null;
        $redirect = array_key_exists($query, $mappings) ? $mappings[$query]["redirect"] : null;
        return new QueryMapping($query, $masterQuery, $redirect);
    }

    private function getCache()
    {
        if (file_exists($this->key) ) {
            return json_decode(file_get_contents($this->key), true);
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
            return time() - $this->cache->getTimestamp($this->key);
        }
        return 0;
    }

    public function resetAge(): void
    {
        touch($this->key);
    }
}