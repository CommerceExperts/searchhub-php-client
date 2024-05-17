<?php

namespace SearchHub\Client;

class QueryMapping {
    /**
     * @var string
     */
    public string $userQuery;

    /**
     * @var string|null
     */
    public ?string $masterQuery;

    /**
     * @var string|null
     */
    public ?string $redirect;



    /**
     * Gets the search query.
     *
     * @return either the mapped master query or in case there is none, the initial user query.
     */
    public function getSearchQuery() :string
    {
       return $this->masterQuery == null ? $this->userQuery : $this->masterQuery;
    }

    public function __construct(string $userQuery, string|null $masterQuery, string|null $redirect)
    {
        $this->userQuery = $userQuery;
        $this->masterQuery = $masterQuery;
        $this->redirect = $redirect;
    }
}

