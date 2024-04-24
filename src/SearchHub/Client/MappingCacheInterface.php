<?php

namespace SearchHub\Client;

interface MappingCacheInterface
{


    /**
     * Get Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param string $query
     *
     * @return array
     * ['masterQuery': 'string', 'redirect': null]
     */
    public function get(string $query): array;

    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param void
     *
     * @return void
     */
    public function deleteCache(): void;

    /**
     * Rewrite cache
     *
     * @param void//TODO
     *
     * @return void
     */
    public function loadCache(array $loadedCache): void;

    //Check if there is cache?
    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param void
     *
     * @return bool
     */
    public function isEmpty(): bool;




}