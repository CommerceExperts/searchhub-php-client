<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubRequest;



$Client1 = new SearchHubClient("Clients\Client1.json");
$Client2 = new SearchHubClient("Clients\Client2.json");
$Client3 = new SearchHubClient("api242323231", "Name3334", "Channel9923");
$Client4 = new SearchHubClient("api25775231", "Name765", "Channel45ß");

echo "$Client1\n$Client2\n$Client3\n$Client4";


echo "Bitte gib einen Query ein >> ";
$input = strval(fgets(STDIN));

$userQuery = trim($input, "\ \n\r\t\v\0"); //Entwerfen "\n", die am ende steht


$searchHubRequest = new SearchHubRequest($userQuery);

$client = new SearchHubClient(",",",",",");
$result = $client->optimize($searchHubRequest);

if ($result->isMapped()) {
    echo "Mapped from: " . $result->getUserQuery() . " to : " . $result->getSearchQuery(). "\n" ;
} else {
    echo "No mapping found!\n";
}

?>
