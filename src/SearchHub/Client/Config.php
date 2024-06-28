<?php

namespace SearchHub\Client;

class Config
{
    /**
     * @var string|null
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
     * @var string
     */
    protected string $stage="prod";

    /**
     * @var string
     */
    protected string $type="saas";

    /**
     * @var string|null
     */
    protected ?string $SaaSEndPoint=null;

    /**
     * Request timeout in seconds. By default - 5//TODO by default? Why 5?
     */
    protected float $requestTimeout = 5;

    /**
     * Report timeout in seconds. By default - 0.5
     */
    protected float $reportTimeout = 0.5;

    /**
     * TTL in seconds
     */
    protected int $mappingCacheTTL = 600;

    public function setClientApiKey(?string $clientApiKey): void
    {
        $this->clientApiKey = $clientApiKey;
    }

    /**
     * @return string
     */
    public function getClientApiKey(): ?string
    {
        return $this->clientApiKey;
    }

    public function setAccountName(string $accountName): void
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

    public function setChannelName(string $channelName): void
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

    public function setStage(string $stage = null): void
    {
        $this->stage = ($stage === "qa") ? "qa" : "prod";
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return $this->stage;
    }

    public function setType(string $type): void
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

    /**
     * $requestTimeout in seconds, can be float value
     */
    public function setRequestTimeout(float $requestTimeout): void
    {
        $this->requestTimeout = $requestTimeout;
    }

    /**
     * @return float
     */
    public function getRequestTimeout(): float
    {
        return $this->requestTimeout;
    }

    /**
     * $reportTimeout in seconds, can be float value
     */
    public function setReportTimeout(float $reportTimeout): void
    {
        $this->reportTimeout = $reportTimeout;
    }

    /**
     * @return float
     */
    public function getReportTimeout(): float
    {
        return $this->reportTimeout;
    }


    public function setMappingCacheTTL(int $mappingCacheTTL): void
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

    public function setSaaSEndPoint(?string $SaaSEndPoint): void
    {
        $this->SaaSEndPoint = $SaaSEndPoint;
    }

    public function getSaaSEndpoint(string $userQuery=null): string
    {
        if ($this->SaaSEndPoint === null){
            return "https://" . ($this->stage === "qa" ? "qa-" : "") . "saas.searchhub.io/smartquery/v2/{$this->accountName}/{$this->channelName}?userQuery={$userQuery}";
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
        //TODO: should here be a folder "searchHub"?
        //return "/tmp/cache/data/cache/";
        return sys_get_temp_dir() . "/";
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
     * @param string|null $SaaSEndPoint
     * @param string|null $clientApiKey Optional. API key required for local mapping for client authentication.
     */
    public function __construct(string $accountName, string $channelName, string $stage, string $type, ?string $SaaSEndPoint = null, ?string $clientApiKey = null)
    {
        $this->setClientApiKey($clientApiKey);
        $this->setAccountName($accountName);
        $this->setChannelName($channelName);
        $this->setStage($stage);
        $this->setType($type);
        $this->setSaaSEndPoint($SaaSEndPoint);
    }
}