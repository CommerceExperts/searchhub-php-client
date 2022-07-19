<?php

namespace SearchHub\Client;

/**
 * Interface SearchHubClientInterface
 *
 * @package SearchHub\Client
 */
interface SearchHubClientInterface
{
    /**
     * Optimize Query by sending it to searchhub checking whether there is a better performing
     * variant of the same search
     *
     * @param SearchHubRequest $searchHubRequest
     *
     * @return SearchHubRequest
     */
    public function optimize(SearchHubRequest $searchHubRequest): SearchHubRequest;

}
