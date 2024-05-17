<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubConstants;
use SearchHub\Client\SearchHubRequest;

//$Client1 = new SearchHubClient("Clients\Client1.json");
//$Client2 = new SearchHubClient("Clients\Client2.json");
//$Client3 = new SearchHubClient("api242323231", "Name3334", "Channel9923", "qa");
//$Client4 = new SearchHubClient("api25775231", "Name765", "Channel45ß", "89");
//
//echo "$Client1\n$Client2\n$Client3\n$Client4";
//
//echo "Bitte gib einen Query ein >> ";
//$input = strval(fgets(STDIN));

//$input="vinylbödeen";
//
//$userQuery = trim($input, "\ \n\r\t\v\0"); //Entwerfen "\n", die am ende steht
//
//$searchHubRequest = new SearchHubRequest($userQuery);
//
//$client = new SearchHubClient(SearchHubConstants::API_KEY, SearchHubConstants::ACCOUNT_NAME,SearchHubConstants::CHANNEL_NAME, "qa");
//$result = $client->optimize($searchHubRequest);
//
//if (!$result->isMapped()) {
//    echo "No mapping found!\n";
//}

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
//    $mappedQuery = $client->mapQuery($query);
//    echo $query . " -> " . $mappedQuery . "\n";
}
