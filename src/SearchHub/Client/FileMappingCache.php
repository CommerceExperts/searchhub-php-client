<?php

namespace SearchHub\Client;

use Twig\Cache\FilesystemCache;

class FileMappingCache implements MappingCacheInterface
{
    /**
     * @var FilesystemCache|null
     */
    private ?FilesystemCache $cache;

    /**
     * @var string|null
     */
    private ?string $key;

    /**
     * Searching locale cache
     */
    public function __construct(Config $config){
        $this->cache = new FilesystemCache("/tmp/cache/data/cache/searchhub/{$config->getAccountName()}/{$config->getChannelName()}/{$config->getStage()}");
        $this->setKey($this->cache->generateKey($config->getAccountName(), $config->getChannelName()));
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
        $this->resetAge();
    }

    public function isEmpty(): bool
    {
        return $this->cache->getTimestamp($this->key) === 0;
    }

    public function lastModifiedDate(): int {
        return $this->cache->getTimestamp($this->key);
    }

    public function resetAge(): void
    {
        touch($this->key);
    }
}