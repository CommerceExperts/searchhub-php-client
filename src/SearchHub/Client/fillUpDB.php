<?php

// Функція для відкриття підключення до бази даних SQLite за допомогою PDO
function openDatabase() {
    try {
        $db = new PDO('sqlite:connections.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        exit();
    }
}

// Функція для створення таблиці в базі даних SQLite, якщо вона ще не існує
function createTableIfNotExists($db) {
    try {
        $query = "CREATE TABLE IF NOT EXISTS connections (mistake TEXT PRIMARY KEY, correct TEXT)";
        $db->exec($query);
    } catch(PDOException $e) {
        echo "Error creating table: " . $e->getMessage();
        exit();
    }
}

// Функція для вставки даних з файлу JSON до бази даних SQLite
function insertDataFromJSON($db) {
    try {
        // Зчитуємо дані з файлу JSON
        $json = file_get_contents('base.json');
        $data = json_decode($json, true);

        // Підготовлюємо запит для вставки даних
        $stmt = $db->prepare("INSERT INTO connections (mistake, correct) VALUES (:mistake, :correct)");

        // Вставляємо дані до таблиці
        foreach ($data['mappings'] as $mapping) {
            $mistake = $mapping['from'];
            $correct = $mapping['to'];
            $stmt->bindParam(':mistake', $mistake);
            $stmt->bindParam(':correct', $correct);
            $stmt->execute();
        }
    } catch(PDOException $e) {
        echo "Error inserting data: " . $e->getMessage();
        exit();
    }
}

// Відкриваємо підключення до бази даних SQLite
$db = openDatabase();

// Створюємо таблицю, якщо вона ще не існує
createTableIfNotExists($db);

// Вставляємо дані з файлу JSON до бази даних SQLite
insertDataFromJSON($db);

// Закриваємо підключення до бази даних SQLite
$db = null;

echo "Data inserted successfully.";

?>
