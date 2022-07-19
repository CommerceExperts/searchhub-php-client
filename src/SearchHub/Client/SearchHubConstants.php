<?php

use Twig\Cache\FilesystemCache;

namespace SearchHub\Client;

class SearchHubConstants
{
    /**
     * @var string
     */
    public const API_KEY = 'request your api key from info@commerce-experts.com';

    /**
     * @var string
     */
    public const ACCOUNT_NAME = 'demo';

    /**
     *
     */
    public const REDIRECTS_BASE_URL = 'https://www.myshopdomain.de/redirectsBaseUrl';

    /**
     * @var string
     */
    public const REQUEST_TIMEOUT = 1000;

    /**
     * TODO: modify, if you have more than one SearchHub Channel
     * @var string
     */
    public const MAPPING_QUERIES_ENDPOINT = sprintf('https://query.searchhub.io/mappingData/v2?tenant=%s.%s', $ACCOUNT_NAME, "de");

    /**
     * TODO: modify, if you have more than one SearchHub Channel
     * @var string
     */
    public const MAPPING_LASTMODIFIED_ENDPOINT = sprintf('https://query.searchhub.io/modificationTime?tenant=%s.%s', $ACCOUNT_NAME, "de");

    /**
     * TODO: modify, if you have more than one SearchHub Channel
     * @var FilesystemCache
     */
    public const MAPPING_CACHE = new FilesystemCache(sprintf('%s/data/cache/searchhub/%s', "/tmp/cache", "de");

    /**
     * TTL in seconds
     */
    public const MAPPING_CACHE_TTL = 600;



}
