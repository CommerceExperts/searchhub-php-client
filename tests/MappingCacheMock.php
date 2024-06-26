<?php

namespace App;

use SearchHub\Client\MappingCacheInterface;
use SearchHub\Client\QueryMapping;

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

    public function get(string $query): QueryMapping
    {
        return new QueryMapping("1", "1", null);
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
            return 9223372036854775807;
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

    public function lastModifiedDate(): int
    {
        if ($this->isOld){
            return 0;

        } else {
            return 9223372036854775807;
        }
    }
}