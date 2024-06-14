<?php

$config = array(
    "clientApiKey" => API_KEY,
    "accountName" => ACCOUNT_NAME,
    "channelName" => CHANNEL_NAME,
    "stage" => "prod",
    "type" => "local" //or SaaS
);

$client = new SearchHubClient($config);
$result = $client->mapQuery($userQuery);

echo($result["userQuery"] . $result["masterQuery"] . $result["redirect"]);
