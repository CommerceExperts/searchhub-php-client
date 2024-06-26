<?php

namespace SearchHub\Client;

interface SearchHubMapperInterface
{
    public function mapQuery(string $userQuery): QueryMapping;

}