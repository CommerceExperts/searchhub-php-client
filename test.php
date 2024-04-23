<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubRequest;


echo "Bitte gib einen Query ein >> ";
$input = strval(fgets(STDIN));

$userQuery = trim($input, "\ \n\r\t\v\0"); //Entwerfen "\n", die am ende steht


$searchHubRequest = new SearchHubRequest($userQuery);

$client = new SearchHubClient();
$result = $client->optimize($searchHubRequest);

if ($result->isMapped()) {
    echo "Mapped from: " . $result->getUserQuery() . " to : " . $result->getSearchQuery(). "\n" ;
} else {
    echo "No mapping found!\n";
}

?>
