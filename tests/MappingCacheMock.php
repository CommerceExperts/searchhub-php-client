<?php

namespace App;

use SearchHub\Client\MappingCacheInterface;
use SearchHub\Client\QueryMapping;
use SearchHub\Client\SearchHubConstants;

class MappingCacheMock  implements MappingCacheInterface
{
    public bool $isEmpty;
    public bool $isOld;

    public bool $updated = false;

    public function __construct(bool $isEmpty, bool $isOld)
    {
        $this->isEmpty = $isEmpty;
        $this->isOld = $isOld;
    }

    public function get(string $query): ?QueryMapping
    {
        return null;
    }

    public function deleteCache(): void
    {
        //
    }

    public function loadCache(array $loadedCache): void
    {
        $this->updated = true;
    }

    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function age(): int
    {
        if ($this->isOld){
            return SearchHubConstants::MAPPING_CACHE_TTL + 1;
        } else {
            return 0;
        }
    }

    public function isUpdated(): bool
    {
        return $this->updated;
    }

    public function resetAge(): void
    {
        //
    }
}