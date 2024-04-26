<?php

namespace SearchHub\Client;

class MappingCache implements MappingCacheInterface
{
    protected $cache; //TODO Datatyp
    protected $accountName;
    protected $channelName;

    /**
     * Get cache and save it
     *
     *
     */
    public function __construct(string $accountName, string $channelName){
        $this->accountName = $accountName;
        $this->channelName = $channelName;
        $this->cache = null;
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
        $this->cache->generateKey($query);   //$this->loadMappings(SearchHubConstants::getMappingQueriesEndpoint($this->accountName, $this->channelName, $this->stage));
        if (isset($mappings[$query])) {
            $mapping = $mappings[$query];
            return $mapping["masterQuery"];
        }
        return "";
    }

    public function deleteCache(): void{
        $this->cache = null;
        $filePath = "/tmp/cache/data/cache/searchhub/{$this->accountName}/{$this->channelName}";
        if (!is_dir($filePath)) return;
        $dir = opendir($filePath);
        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $filePath . '/' . $file;
            if (is_file($fullPath)) unlink($fullPath);
        }
        closedir($dir);
    }

    public function loadCache(array $loadedCache): void{
        //$this->cache = $loadedCache;
        foreach ($loadedCache as $key => $value) {
            $this->cache->write($key, $value);
        }

    }

    public function getCache(): \Twig\Cache\FilesystemCache
    {
        return $this->cache;
    }

    public function isEmpty(): bool
    {
        //$filePath = "/tmp/cache/data/cache/searchhub/{$this->accountName}/{$this->channelName}";
        //return count(glob($filePath . '/*')) === 0;
        return $this->cache === null;
    }
}