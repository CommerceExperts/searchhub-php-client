<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubConstants;


$config = array(
    "clientApiKey" => SearchHubConstants::API_KEY,
    "accountName" => "test",
    "channelName" => "working",
    "stage" => "qa"
);

$test = array ("vinil click", "sichtschuztzäune", "klick-vinyl", "aboba", "sichtschutz zaune");

foreach ($test as $query)
{
    $client = new SearchHubClient($config);
    $client->optimize($query);
}

