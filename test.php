<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubConstants;


$config = array(
    "clientApiKey" => SearchHubConstants::API_KEY,
    "accountName" => "test",
    "channelName" => "working",
    "stage" => "qa",
    "type" => "SaaS" //or  local
);

//$test = array ("vinil click", "sichtschuztzäune", "klick-vinyl", "aboba", "sichtschutz zaune",
//    "außen wand leuchte", "waschbecken- unterschrank", "feder nut bretter", "kette säge", "außenleuchten mit bewegungsmelder");

$test = array ("\"vinil click\"", "\"sichtschuztzäune\\", "\\klick-vinyl", "\"aboba\\", "\"aboba\"", "Cola \"Coca\"");

$start = microtime(true);
for($i = 1; $i <= 1; $i++){
    foreach ($test as $query)
    {
        $client = new SearchHubClient($config);
        $client->mapQuery($query);
    }
}

$executionTime = microtime(true) -  $start;

echo "\n\n\t\t10 query:\n" . "Total time: " . $executionTime . "s\nAverage time: " . $executionTime/10 . "s";
