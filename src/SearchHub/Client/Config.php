<?php

namespace SearchHub\Client;

class Config
{
    public function __construct($apiKey, $accountName, $channelName, $stage, $type){
        $config = array(
            "clientApiKey" => API_KEY::API_KEY,
            "accountName" => "test",
            "channelName" => "working",
            "stage" => "qa",
            "type" => "local");//or SaaS);
    }
}