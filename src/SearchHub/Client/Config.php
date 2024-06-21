<?php

namespace SearchHub\Client;

class Config
{
    /**
     * @var string
     */
    protected ?string $clientApiKey;

    /**
     * @var string
     */
    protected string $accountName;

    /**
     * @var string
     */
    protected string $channelName;

    /**
     * @var string|null
     */
    protected ?string $stage="prod";

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var ?string
     */
    protected ?string $SaaSEndPoint=null;

    /**
     * Request timeout in milliseconds
     */
    protected int $requestTimeout = 1000;

    /**
     * TTL in seconds
     */
    protected int $mappingCacheTTL = 600;

    public function setClientApiKey($clientApiKey)
    {
        $this->clientApiKey = $clientApiKey;
    }

    /**
     * @return string
     */
    public function getClientApiKey(): string
    {
        return $this->clientApiKey;
    }

    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
    }

    /**
     * @return string
     */
    public function getAccountName(): string
    {
        return $this->accountName;
    }

    public function setChannelName($channelName)
    {
        $this->channelName = $channelName;
    }

    /**
     * @return string
     */
    public function getChannelName(): string
    {
        return $this->channelName;
    }

    public function setStage($stage = null)
    {
        $this->stage = ($stage === "qa") ? "qa" : "prod";
    }

    /**
     * @return string
     */
    public function getStage(): ?string
    {
        return $this->stage;
    }

    public function setType($type)
    {
        $this->type = ($type !== "local") ? "saas" :  "local";
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function setRequestTimeout(int $requestTimeout)
    {
        $this->requestTimeout = $requestTimeout;
    }

    /**
     * @return int
     */
    public function getRequestTimeout(): int
    {
        return $this->requestTimeout;
    }

    public function setMappingCacheTTL(int $mappingCacheTTL)
    {
        $this->mappingCacheTTL = $mappingCacheTTL;
    }

    /**
     * @return int
     */
    public function getMappingCacheTTL(): int
    {
        return $this->mappingCacheTTL;
    }

    public function setSaaSEndPoint($SaaSEndPoint)
    {
        $this->SaaSEndPoint = $SaaSEndPoint;
    }

    public function getSaaSEndpoint(string $userQuery=null): ?string
    {
        if ($this->SaaSEndPoint === null){
            return "https://{$this->stage}-saas.searchhub.io/smartquery/v2/{$this->accountName}/{$this->channelName}?userQuery={$userQuery}";
        } else {
            return $this->SaaSEndPoint ."?userQuery=". $userQuery;
        }
    }

    /**
     * Endpoint to asynchronously send mapping statistics
     * @return string
     */
    public function getMappingDataStatsEndpoint(): string
    {
        return "https://" . ($this->stage === "qa" ? "qa-" : "") . "import.searchhub.io/reportStats";
    }

    /**
     * @return string
     */
    public function getMappingQueriesEndpoint(): string
    {
        return "https://" . ($this->stage === "qa" ? "qa-" : "") . "api.searchhub.io/mappingData/v2?tenant={$this->accountName}.{$this->channelName}";
    }

    /**
     * @return string
     */
    public function getSaaSLastModifiedDateEndpoint(): string
    {
        return "https://" . ($this->stage === "qa" ? "qa-" : "") . "api.searchhub.io/modificationTime?tenant={$this->accountName}.{$this->channelName}";
    }

    /**
     * @return string
     */
    public function getFileSystemCacheDirectory(): string
    {
        return "/tmp/cache/data/cache/searchhub/{$this->accountName}/{$this->channelName}/{$this->stage}";
    }

    /**
     * Constructor for initializing an instance of a class.
     *
     * Initializes the object with provided parameters, setting up the account name,
     * channel name, stage/environment, type of mapper, optional custom SaaS endpoint,
     * and client API key if applicable.
     *
     * @param string $accountName The name of the account
     * @param string $channelName The name of the channel
     * @param string $stage The stage/environment  ("qa" or "prod").
     * @param string $type The type of mapper being instantiated ("local" or "saas").
     * @param string|null $customSaaSEndPoint Optional. Custom SaaS endpoint URL for SaaS mapping.
     *                                      Format example: "customURL=$query".
     * @param string|null $clientApiKey Optional. API key required for local mapping for client authentication.
     */
    public function __construct(string $accountName, string $channelName, string $stage, string $type, ?string $customSaaSEndPoint = null, ?string $clientApiKey = null)
    {
        $this->setClientApiKey($clientApiKey);
        $this->setAccountName($accountName);
        $this->setChannelName($channelName);
        $this->setStage($stage);
        $this->setType($type);
        $this->setSaaSEndPoint($customSaaSEndPoint);
    }
}