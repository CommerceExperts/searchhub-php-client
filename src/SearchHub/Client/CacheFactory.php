<?php

namespace SearchHub\Client;

class CacheFactory
{
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

    public function setAccountName($accountName): CacheFactory
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


    public function setChannelName($channelName): CacheFactory
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

    public function setStage($stage = null): CacheFactory
    {
        $this->stage = ($stage === "qa") ? "qa" : "prod";
        return $this;
    }

    public function getStage(): ?string
    {
        return $this->stage;
    }

    public function createCache(): SQLCache|MappingCache
    {
        try
        {
            //Try to connect to db
            return new SQLCache($this->accountName, $this->channelName, $this->stage);
        }

        catch(\Exception $e)
        {
            //If not connected to DB - use local Cache
            return new MappingCache($this->accountName, $this->channelName, $this->stage);
        }
    }
}