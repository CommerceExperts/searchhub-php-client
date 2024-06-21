<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\API_KEY;
use SearchHub\Client\Config;
use SearchHub\Client\SearchHubClient;


$config = new Config(API_KEY::API_KEY, "test", "working", "qa", "local");

//$test = array ("vinil click", "sichtschuztzäune", "klick-vinyl", "aboba", "sichtschutz zaune",
//    "außen wand leuchte", "waschbecken- unterschrank", "feder nut bretter", "kette säge", "außenleuchten mit bewegungsmelder");

$test = array ("\"vinil click\"", "\"sichtschuztzäune\\", "\\klick-vinyl", "\"aboba\\", "\"feder. nut bretter\"", "Cola \"Coca\"", "123", "finylböden", "wandaussenleuchten", "waschbecken mit untershrank");
$number = 1;

$numberOfQueries = $number * count($test);

$start = microtime(true);

for($i = 1; $i <= $number; $i++){
    foreach ($test as $query)
    {
        $client = new SearchHubClient($config);
        $mappedQuery = $client->mapQuery($query);
        echo"$query -> $mappedQuery->masterQuery\n";
    }
}

$executionTime = microtime(true) -  $start;

echo "\n\n\t\t$numberOfQueries query:\n" . "Total time: " . $executionTime . "s\nAverage time: " . $executionTime/$numberOfQueries . "s";
