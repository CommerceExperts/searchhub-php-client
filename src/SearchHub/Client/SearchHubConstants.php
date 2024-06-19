<?php

namespace SearchHub\Client;

use Twig\Cache\FilesystemCache;


class SearchHubConstants
{


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
     * @param $ACCOUNT_NAME
     * @param $CHANNEL_NAME
     * @param $stage
     * @return string
     */
    public static function getMappingQueriesEndpoint($ACCOUNT_NAME, $CHANNEL_NAME, $stage): string
    {
        if ($stage === "qa"){
            return "https://qa-api.searchhub.io/mappingData/v2?tenant={$ACCOUNT_NAME}.{$CHANNEL_NAME}";
        } else {
            return "https://api.searchhub.io/mappingData/v2?tenant={$ACCOUNT_NAME}.{$CHANNEL_NAME}";
        }

    }

    /**
     * TODO: modify, if you have more than one SearchHub Channel
     * @param $CHANNEL_NAME
     * @param $ACCOUNT_NAME
     * @return string
     */
    public static function getMappingLastModifiedEndpoint($ACCOUNT_NAME, $CHANNEL_NAME, $stage): string
    {
        if ($stage === "qa"){
            return "https://qa-api.searchhub.io/modificationTime?tenant={$ACCOUNT_NAME}.{$CHANNEL_NAME}";
        } else {
            return "https://api.searchhub.io/modificationTime?tenant={$ACCOUNT_NAME}.{$CHANNEL_NAME}";
        }
    }

    /**
     * TTL in seconds
     */
    public const MAPPING_CACHE_TTL = 600;

    /**
     * Endpoint to asynchronously send mapping statistics
     */


    public static function getMappingDataStatsEndpoint(string $stage): string
    {
        return ($stage === "qa") ? 'https://qa-import.searchhub.io/reportStats': 'https://import.searchhub.io/reportStats';
    }

    public static function getSaaSEndpoint(string $stage, $accountName, $channelName, $query): string
    {
        return "https://{$stage}-saas.searchhub.io/smartquery/v2/{$accountName}/{$channelName}?userQuery=$query";
    }
}
