<?php

namespace SearchHub\Client;

use PDO;

class SQLCache implements MappingCacheInterface
{
    /**
     * @var PDO|null
     */
    private ?PDO $db;

    private string $SQLName;


    public function __construct(Config $config)
    {
        $this->SQLName = "database.{$config->getAccountName()}.{$config->getChannelName()}.{$config->getStage()}.sqlite";
        $this->db = new PDO('sqlite:'. $this->SQLName);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='queries'");
        //If table don´t exist - create
        if ($result->fetch() === false) {
            $createTableQuery = "
            CREATE TABLE queries (
            userQuery VARCHAR(255) PRIMARY KEY,
            masterQuery VARCHAR(255),
            redirect VARCHAR(255)
        )";
            $this->db->exec($createTableQuery);
        }
    }

    public function get(string $query): QueryMapping
    {
        $query = mb_strtolower($query);
        //Search query in DB

        $stmt = $this->db->prepare("SELECT * FROM queries WHERE userQuery = :userQuery");
        $stmt->bindParam(':userQuery', $query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return new QueryMapping($query, $result ? $result["masterQuery"] : $query, $result ? $result["redirect"] : null);
    }

    public function loadCache(array $loadedCache): void
    {
        // Start transaction
        $this->db->beginTransaction();

        foreach ($loadedCache as $query => $arr) {
            $masterQuery = $arr["masterQuery"];
            $redirect = $arr["redirect"];

                $stmt = $this->db->prepare("
                INSERT OR IGNORE INTO queries (userQuery, masterQuery, redirect)
                VALUES (:userQuery, :masterQuery, :redirect)
            ");
                $stmt->bindParam(':userQuery', $query);
                $stmt->bindParam(':masterQuery', $masterQuery);
                $stmt->bindParam(':redirect', $redirect);
                $stmt->execute();
        }
        //commit transaction
        $this->db->commit();

        $this->resetAge();
    }

    public function deleteCache(): void
    {
        $this->db->exec("DELETE FROM queries");
    }

    public function isEmpty(): bool
    {
        $result = $this->db->query("SELECT COUNT(*) as count FROM queries");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['count'] == 0;
    }

    public function lastModifiedDate(): int
    {
        return filemtime($this->SQLName);
    }

    public function resetAge(): void
    {
        touch($this->SQLName);
    }
}


