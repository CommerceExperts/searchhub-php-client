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

    /**
     * @var string
     */
    protected $type;

    public function setClientApiKey($clientApiKey)
    {
        $this->clientApiKey = $clientApiKey;
    }

    /**
     * @return string|null
     */
    public function getClientApiKey(): ?string
    {
        return $this->clientApiKey;
    }

    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
    }

    /**
     * @return string|null
     */
    public function getAccountName(): ?string
    {
        return $this->accountName;
    }

    public function setChannelName($channelName)
    {
        $this->channelName = $channelName;
    }

    /**
     * @return string|null
     */
    public function getChannelName(): ?string
    {
        return $this->channelName;
    }

    public function setStage($stage = null)
    {
        $this->stage = ($stage === "qa") ? "qa" : "prod";
    }

    /**
     * @return string|null
     */
    public function getStage(): ?string
    {
        return $this->stage;
    }

    public function setType($type)
    {
        $this->type = ($type !== "local") ? "SaaS" :  "local";
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    public function __construct($clientApiKey, $accountName, $channelName, $stage, $type){
        $this->setClientApiKey($clientApiKey);
        $this->setAccountName($accountName);
        $this->setChannelName($channelName);
        $this->setStage($stage);
        $this->setType($type);
    }
}