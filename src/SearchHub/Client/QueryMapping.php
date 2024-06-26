<?php

namespace SearchHub\Client;

class QueryMapping {
    /**
     * @var string
     */
    public string $userQuery;

    /**
     * @var string
     */
    public string $masterQuery;

    /**
     * @var string|null
     */
    public ?string $redirect;

    /**
     * Sets the search query.
     *
     * @sets either the mapped master query or in case there is none, the initial user query.
     */
    private function setSearchQuery(?string $masterQuery) : void
    {
        $this->masterQuery = $masterQuery ?? $this->userQuery;
    }

    public function __construct(string $userQuery, ?string $masterQuery, ?string $redirect)
    {
        $this->userQuery = $userQuery;
        $this->setSearchQuery($masterQuery);
        $this->redirect = $redirect;
    }
}

