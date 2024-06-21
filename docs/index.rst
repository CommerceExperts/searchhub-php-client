Module: smartQueryPHP
=====================

smartQueryPHP is a searchHub module that maps similar queries (e.g., "skate board", "skate boarding", "skateboards", "skateboard", "scate burd") to the best[1] query - the so-called MasterQuery. The smartQueryPHP module automatically connects to the searchHub API to retrieve required data and provide statistics and performance information about the module and its mapping process.

We recommend following best practices to seamlessly integrate smartQueryPHP into your system.

.. [1] Additionally, there are use cases where ambiguous mappings result in multiple MasterQueries. These cases will receive differentiated handling in the future. It's important to note that the term "best query" refers to the keyword with the most positive outcomes in terms of user interaction and economic profit.

Sections
--------

- Common Concepts and Behavior
- Direct Integration
    - Requirements
    - Composer Dependency
    - Essential Usage
    - Usage Example
- Best Practices
    - Basic smartQueryPHP Implementation
    - Query Correction Feedback
    - Potential Correction Alternatives
    - SearchHub Redirects

Common Concepts and Behavior
============================

Query Naming
------------

When discussing query mapping, we distinguish between the following types of queries:

- **User Query**: The original text a user enters in the search box, representing their unrefined search intent.
- **Master Query**: The refined and optimized search term sent to the search engine to retrieve the most relevant results.

Query Mapping
-------------

- If the user query can be mapped, a different search query might be returned. This could be the masterQuery or another optimized term.
- If the user query is already identified as the "best query" (meaning it historically yields optimal results), it's mapped back to itself, considered a successful mapping.
- If the user query cannot be mapped, it's sent directly to the search engine. The integration methods and endpoints return "rich objects" containing additional information.

Bypassing Queries
-----------------

Enclosing a search term in double quotes ("example") forces it to be treated as an unknown query. This bypasses query mapping and sends the original term directly to the search engine, even if a mapping exists.

Direct Integration
==================

Requirements
------------

- PHP version ^7.4 || ^8.0
- Approximately 120MB to 300MB additional memory (depending on the data volume managed)
- If using a firewall, configure it to allow connections to the following HTTPS endpoints:
    - https://api.searchhub.io/
    - https://import.searchhub.io/
    - https://prod-saas.searchhub.io/

Composer Dependency
-------------------

The smartQueryPHP library can be added as a composer dependency `searchhub-php-client`.

.. code-block:: json

   {
     "require": {
       "searchhub-php-client": "1.0.0"
     }
   }

Essential Usage
---------------

The library encompasses the following core components:

Config
------
Initializes the object with provided parameters, setting up the account name, channel name, stage/environment, type of mapper, optional custom SaaS endpoint, and client API key if applicable.

- @param string $accountName: The account name.
- @param string $channelName: The channel name.
- @param string $stage: The stage/environment ("qa" or "prod").
- @param string $type: The type of mapper instantiated ("local" or "saas").
- @param string|null $customSaaSEndPoint: Optional. Custom SaaS endpoint URL for SaaS mapping. Format example: "customURL=$query".
- @param string|null $clientApiKey: Optional. API key required for local mapping for client authentication.

SearchHubClient
---------------

The central component of the smartQueryPHP library is SearchHubClient, facilitating query mappings via the `mapQuery` method. This class manages QueryMappers, initializing and configuring them based on provided parameters. It supports both LocalMapper and SaaSMapper functionalities as per configuration requirements. The instantiation requires a valid API key[2].

.. [2] A personal API Key will be provided.

SearchHubClient mapQuery(String input)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This method returns a QueryMapping object containing the original user query, a mapped query, and an optional redirect URL.

QueryMapping
------------

A PHP object encapsulating key query details: "user query", "master query", and "redirection".

SaaSMapper
----------

The SaaSMapper class communicates directly with the SaaS server to retrieve the master query, leveraging a predefined endpoint for data access.

LocalMapper
-----------

The LocalMapper class optimizes data retrieval through intelligent utilization of in-memory and SQL caches. It prioritizes cached data for quicker response times, simultaneously fetching and caching data from masterQuery when required.

LocalMapper report (Strings input)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This method aggregates user input statistics, enhancing user input data for improved overall experience.

MappingDataUpdate
-----------------

Vital for maintaining current mapping data in local and SQL caches. Recommended to invoke the `updateMappingData` method every 10 minutes for data freshness, reduced server load, improved performance, and resilience.

MappingDataUpdate updateMappingData(Config $config, $cache, $httpClient)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This function retrieves, processes, and stores mapping data from a remote server, accepting configuration details, cache object, and HTTP client object. Note that `$cache` and `$httpClient` arguments are optional, with default implementations available if not provided explicitly.

Usage Example
-------------

.. code-block:: php

   <?php

   $config = new Config("accountName", "channel", "prod", "saas", null, "apiKey");

   $client = new SearchHubClient($config);
   $result = $client->mapQuery($userQuery);

   echo $result["userQuery"] . $result["masterQuery"] . $result["redirect"];

Best Practices
==============

Basic smartQueryPHP Implementation
----------------------------------

Story
^^^^^

As a customer, I want to see search results optimized using CXP searchHub (https://docs.searchhub.io/) and frequently updated using recent KPI data.

Acceptance criteria
^^^^^^^^^^^^^^^^^^^

- Search phrases are validated and optimized using searchHub’s smartQueryPHP Module (https://docs.searchhub.io/searchhub-php-client.html) before submitting to the internal search engine.
- Internal systems can access https://query.searchhub.io/ for search phrase validation.
- Internal systems can access https://import.searchhub.io/ for data exchange and updates.

Query Correction Feedback
-------------------------

Story
^^^^^

As a customer, I want to see the corrected query and retain the option to search using my original input.

Acceptance criteria
^^^^^^^^^^^^^^^^^^^

- Display a message if a query is corrected.
- Provide a link enabling users to search using the original query instead.
- Clicking the link should direct to search without further mapping.

Technical hint
^^^^^^^^^^^^^^

- Use the bypass feature of smartQueryPHP by enclosing queries in quotes to avoid mapping.

Potential Correction Alternatives
---------------------------------

Story
^^^^^

As a user, I expect to see alternative versions of my misspelled query if not automatically corrected through direct mapping.

Acceptance Criteria
^^^^^^^^^^^^^^^^^^^

- Display potential correction suggestions ("Did you mean…") if automatic correction fails.
- Allow clicking on suggested queries to replace the current user query.

SearchHub Redirects
-------------------

Story
^^^^^

As a search manager, I want users redirected to landing pages based on configurations in searchHub.

Acceptance criteria
^^^^^^^^^^^^^^^^^^^

- Redirect configured queries to specified landing pages or URLs.
