<?php

namespace SearchHub\Client;

interface MappingCacheInterface
{
    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @return QueryMapping
     */
    public function get(string $query): QueryMapping;

    public function deleteCache(): void;


    /**
     * Rewrite cache
     *
     * @param array $loadedCache in form [$query => {"masterQuery" => "", "redirect" => ""}, ...]
     * @return void
     */
    public function loadCache(array $loadedCache): void;

    public function isEmpty(): bool;

    public function lastModifiedDate(): int;

    public function resetAge(): void;

}