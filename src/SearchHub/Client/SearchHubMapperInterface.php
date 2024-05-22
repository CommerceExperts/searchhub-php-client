<?php

namespace SearchHub\Client;

interface SearchHubMapperInterface
{
    public function mapQuery($userQuery): QueryMapping;

}