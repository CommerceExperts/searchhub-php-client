<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\ClientDB;
use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubConstants;


$config = array(
    "clientApiKey" => SearchHubConstants::API_KEY,
    "accountName" => "test",
    "channelName" => "working",
    "stage" => "qa"
);

$test = array ("vinil click", "sichtschuztzäune", "klick-vinyl", "aboba", "sichtschutz zaune", "außen wand leuchte", "waschbecken- unterschrank", "feder nut bretter", "kette säge", "außenleuchten mit bewegungsmelder");

$TimeCache = 0;

$TimeDB = 0;




for($i = 1; $i <= 1000; $i++){
    //echo "-------------------------------CACHE-------------------------------\n";
    $startTimeCache = microtime(true);
    foreach ($test as $query)
    {
        $clientCache = new SearchHubClient($config);
        $clientCache->optimize($query);
    }
    $TimeCache += microtime(true) -  $startTimeCache;

    //echo "\n\n\n-------------------------------DATA BASE-------------------------------\n";
    $startTimeDB = microtime(true);
    foreach ($test as $query)
    {
        $clientDB = new ClientDB($config);
        $clientDB->optimize($query);
    }
    $TimeDB += microtime(true) -  $startTimeDB;
}

echo "\n\n-------------------------------CACHE-------------------------------\n" . "Total time: " . $TimeCache . "s\nAverage time: " . $TimeCache/10000 . "s";
echo "\n-----------------------------DATA BASE-----------------------------\n" . "Total time: " . $TimeDB . "s\nAverage time: " . $TimeDB/10000 . "s";

if ($TimeCache > $TimeDB){
    echo "\n\nDB is x" . $TimeCache / $TimeDB . " times ";
} else {
    echo "\n\nCache is x" . $TimeDB / $TimeCache . " times";
}
//$client = new ClientDB($config);
//$client->optimize("vinil click");
