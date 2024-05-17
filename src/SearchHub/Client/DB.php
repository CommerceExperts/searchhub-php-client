<?php

namespace SearchHub\Client;

use PDO;
use PDOException;

class DB
{
    /**
     * @var PDO|null
     */
    protected ?PDO $db;

    public function __construct()
    {
        try {
            $this->db = new PDO('sqlite:my_database.sqlite');
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
        } catch (PDOException $e) {
            //TODO: log
        }
    }

    public function get(string $query): QueryMapping
    {
        //Search query in DB
        try {
            $stmt = $this->db->prepare("SELECT * FROM queries WHERE userQuery = :userQuery");
            $stmt->bindParam(':userQuery', $query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            //TODO log
        }
        return new QueryMapping($query, $result ? $result["masterQuery"] : null, $result ? $result["redirect"] : null);
    }

    public function loadDB(array $loadedData): void
    {
        foreach ($loadedData as $query => $arr){
            $masterQuery = $arr["masterQuery"];
            $redirect = $arr["redirect"];

            try
            {
                $stmt = $this->db->prepare("
                INSERT OR IGNORE INTO queries (userQuery, masterQuery, redirect)
                VALUES (:userQuery, :masterQuery, :redirect)
            ");
                $stmt->bindParam(':userQuery', $query);
                $stmt->bindParam(':masterQuery', $masterQuery);
                $stmt->bindParam(':redirect', $redirect);
                $stmt->execute();
            } catch (PDOException $e) {
                //TODO log
            }
        }
    }

    public function deleteDB(): void
    {
        try {
            $this->db->exec("DELETE FROM queries");
        } catch (PDOException $e) {
            //TODO log
        }
    }

    public function isEmpty(): bool
    {
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM queries");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row['count'] == 0;
        } catch (PDOException $e) {
            return false;
        }
    }

}