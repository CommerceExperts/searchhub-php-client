<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\SearchHubClient;
use SearchHub\Client\SearchHubRequest;

while (True) {
    echo "Bitte gib einen Query ein >> ";
    $input = strval(fgets(STDIN));

    $userQuery = substr_replace(preg_replace('/^\s+/', '', $input), "", -1); //Entwerfen alle unnötige " " und "\n"

    if (!rtrim($userQuery, " ")) { //Cheak, ob etwas gegeben wurde
        echo "Sie haben nichts gegeben. Versuchen Sie noch mal\n";
    } else {
        if ($userQuery == "exit") {
            echo "Danke auf Ihre Zeit";
            break;
        } else {
            $searchhubRequest = new SearchHubRequest($userQuery);
            $client = new SearchHubClient();
            $result = $client->optimize($searchhubRequest);

            echo $result;

            if (strcmp($result->getUserQuery(), $result->getSearchQuery())) {
                echo "Mapped to: " . $result->getSearchQuery()."\n";
            } else {
                echo "No mapping found!\n";
            }
            echo $result->getSearchQuery();
        }
    }
}

?>
