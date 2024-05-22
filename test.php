<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubConstants;


$config = array(
    "clientApiKey" => SearchHubConstants::API_KEY,
    "accountName" => "test",
    "channelName" => "working",
    "stage" => "qa",
    "type" => "local" //or saas
);

$test = array ("vinil click", "sichtschuztzäune", "klick-vinyl", "aboba", "sichtschutz zaune",
    "außen wand leuchte", "waschbecken- unterschrank", "feder nut bretter", "kette säge", "außenleuchten mit bewegungsmelder");

$start = microtime(true);
for($i = 1; $i <= 5; $i++){
    foreach ($test as $query)
    {
        $clientDB = new SearchHubClient($config);
        $clientDB->mapQuery($query);
    }
}

$executionTime = microtime(true) -  $start;

echo "\n\n\t\t50 query:\n" . "Total time: " . $executionTime . "s\nAverage time: " . $executionTime/50 . "s";
