<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\Config;
use SearchHub\Client\SearchHubClient;


$config = new Config( "test", "working", "qa", "saas", null, getenv('SH_API_KEY'));



$test = array ("\"vinil click\"", "\"sichtschuztzäune\\", "\\klick-vinyl", "\"aboba\\", "\"feder. nut bretter\"", "Cola \"Coca\"", "123", "finylböden", "wandaussenleuchten", "waschbecken mit untershrank");
$number = 1;

$numberOfQueries = $number * count($test);

echo"\tSaaS mapper:";
$start = microtime(true);
$client = new SearchHubClient($config);

for($i = 1; $i <= $number; $i++){
    foreach ($test as $query)
    {
        $mappedQuery = $client->mapQuery($query);
        //echo"$query -> $mappedQuery->masterQuery\n";
        //echo json_encode($query);
    }
}

$executionTime = microtime(true) -  $start;

echo "\t\t$numberOfQueries query:\n" . "Total time: " . $executionTime . "s\nAverage time: " . $executionTime/$numberOfQueries . "s";


echo"\n\n\n\n\tLocal mapper:";
$config = new Config( "test", "working", "qa", "local", null, getenv('SH_API_KEY'));
$start = microtime(true);

$client = new SearchHubClient($config);

for($i = 1; $i <= $number; $i++){
    foreach ($test as $query)
    {
        $mappedQuery = $client->mapQuery($query);
        //echo"$query -> $mappedQuery->masterQuery\n";
    }
}

$executionTime = microtime(true) -  $start;

echo "\t\t$numberOfQueries query:\n" . "Total time: " . $executionTime . "s\nAverage time: " . $executionTime/$numberOfQueries . "s";
