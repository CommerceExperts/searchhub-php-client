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
     * @var string
     */
    public const CHANNEL_NAME = 'de';

    /**
     * The base url to prepend to relative redirect urls
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
    public const MAPPING_QUERIES_ENDPOINT = sprintf('https://query.searchhub.io/mappingData/v2?tenant=%s.%s', $ACCOUNT_NAME, $CHANNEL_NAME);

    /**
     * TODO: modify, if you have more than one SearchHub Channel
     * @var string
     */
    public const MAPPING_LASTMODIFIED_ENDPOINT = sprintf('https://query.searchhub.io/modificationTime?tenant=%s.%s', $ACCOUNT_NAME, $CHANNEL_NAME);

    /**
     * TODO: modify, if you have more than one SearchHub Channel
     * @var FilesystemCache
     */
    public const MAPPING_CACHE = new FilesystemCache(sprintf('%s/data/cache/searchhub/%s/%s', "/tmp/cache", $ACCOUNT_NAME, $CHANNEL_NAME);

    /**
     * TTL in seconds
     */
    public const MAPPING_CACHE_TTL = 600;

    /**
     * Endpoint to asynchronously send mapping statistics
     */
    public const MAPPING_STATS_ENDPOINT = 'https://import.searchhub.io/reportStats'


}
