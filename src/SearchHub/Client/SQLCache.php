<?php

namespace SearchHub\Client;

use Error;
use Exception;
use PDO;

class SQLCache implements MappingCacheInterface
{
    /**
     * @var PDO
     */
    private PDO $db;

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

    /**
     * @throws Exception
     */
    public function deleteCache(): void
    {
        $result = $this->db->query("SELECT COUNT(*) FROM queries");
        $rowCount = $result->fetchColumn();

        if ($rowCount > 0) {
            $this->db->exec("DELETE FROM queries");
        }
    }

    public function isEmpty(): bool
    {
        $result = $this->db->query("SELECT COUNT(*) as count FROM queries");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['count'] == 0;
    }

    public function lastModifiedDate(): int
    {
        $filetime = filemtime($this->SQLName);
        if ($filetime === false) { // Коректна перевірка на false
            throw new Error("Cannot access DB");
        }
        return $filetime;
    }

    public function resetAge(): void
    {
        touch($this->SQLName);
    }
}


