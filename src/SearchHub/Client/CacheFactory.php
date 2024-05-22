<?php

namespace SearchHub\Client;

class CacheFactory
{
    public function __construct($config)
    {
        if (isset($config['accountName'])) {
            $this->setAccountName($config['accountName']);
        }
        if (isset($config['channelName'])) {
            $this->setChannelName($config['channelName']);
        }
        if (isset($config['stage'])) {
            $this->setStage($config['stage']);
        }
    }

    public function setAccountName($accountName): ?SearchHubClient
    {
        $this->accountName = $accountName;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getAccountName(): ?string
    {
        return $this->accountName;
    }


    public function setChannelName($channelName): ?SearchHubClient
    {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getChannelName(): ?string
    {
        return $this->channelName;
    }

    public function setStage($stage = null): ?SearchHubClient
    {
        $this->stage = ($stage === "qa") ? "qa" : "prod";
        return $this;
    }

    public function getStage(): ?string
    {
        return $this->stage;
    }
}