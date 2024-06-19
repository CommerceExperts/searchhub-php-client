<?php

namespace SearchHub\Client;

use GuzzleHttp\ClientInterface;

class Config
{
    /**
     * @var string|null
     */
    protected $clientApiKey;

    /**
     * @var string|null
     */
    protected $accountName;

    /**
     * @var string|null
     */
    protected $channelName;

    /**
     * @var string
     */
    protected $stage;

    public function setClientApiKey($clientApiKey): LocalMapper
    {
        $this->clientApiKey = $clientApiKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientApiKey(): ?string
    {
        return $this->clientApiKey;
    }

    public function setAccountName($accountName): LocalMapper
    {
        $this->accountName = $accountName;
        return $this;
    }
    public function __construct($apiKey, $accountName, $channelName, $stage, $type){
        $config = array(
            "clientApiKey" => API_KEY::API_KEY,
            "accountName" => "test",
            "channelName" => "working",
            "stage" => "qa",
            "type" => "local");//or SaaS);
    }
}