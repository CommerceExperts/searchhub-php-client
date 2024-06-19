<?php

namespace SearchHub\Client;

use Exception;
use PDO;
use PDOException;

class SQLCache implements MappingCacheInterface
{
    /**
     * @var PDO|null
     */
    protected ?PDO $db;

    protected string $SQLName;


    public function __construct($accountName, $channelName, $stage)
    {
        $this->SQLName = "database.$accountName.$channelName.$stage.sqlite";
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
        )
        ";
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

    public function age(): int
    {
        if (file($this->SQLName)){
            return time() - filemtime($this->SQLName);
        }
        return 0;
    }

    public function resetAge(): void
    {
        touch($this->SQLName);
    }
}