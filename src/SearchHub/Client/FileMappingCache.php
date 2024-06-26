<?php

namespace SearchHub\Client;

use Exception;
use Twig\Cache\FilesystemCache;

class FileMappingCache implements MappingCacheInterface
{
    /**
     * @var FilesystemCache
     */
    private FilesystemCache $cache;

    /**
     * @var string
     */
    private string $key;

    /**
     * Searching locale cache
     */
    public function __construct(Config $config){
        $this->cache = new FilesystemCache($config->getFileSystemCacheDirectory());
        $this->setKey($this->cache->generateKey($config->getAccountName(), $config->getChannelName()));
    }

    private function setKey(string $key): void
    {
        $this->key = $key;
    }


    /**
     * @throws Exception
     */
    public function get(string $query): QueryMapping
    {
        $query = mb_strtolower($query);
        $mappings = $this->getCache();
        $masterQuery = array_key_exists($query, $mappings) ? $mappings[$query]["masterQuery"] : null;
        $redirect = array_key_exists($query, $mappings) ? $mappings[$query]["redirect"] : null;
        return new QueryMapping($query, $masterQuery, $redirect);
    }

    /**
     * @throws Exception
     */
    private function getCache(): array
    {
        if (file_exists($this->key))
        {
            $json = file_get_contents($this->key);

            if ($json === false)
            {
                throw new Exception("Failed to read file: {$this->key}");
            }

            if (empty($json))
            {
                throw new Exception("File is empty: {$this->key}");
            }

            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE)
            {
                throw new Exception("Failed to parse JSON: " . json_last_error_msg());
            }

            return $data;
        }
        else
        {
            throw new Exception("File does not exist: {$this->key}");
        }
    }


    public function deleteCache(): void
    {
        if (file_exists($this->key)) {
            unlink($this->key);
        }
    }

    /**Uses for loading local file cache
    $loadedCache = [
    'casaya aussenleuchten' => [
        'masterQuery' => 'casaya aussenleuchte',
        'redirect' => ''
    ],
    ....
    ];

    */
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