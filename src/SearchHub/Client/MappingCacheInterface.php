<?php

namespace SearchHub\Client;

interface MappingCacheInterface
{

    public function get(string $query): string;

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
    public function loadCache(string $loadedCache): void;

    //Check if there is cache?
    public function isEmpty(): bool;




}