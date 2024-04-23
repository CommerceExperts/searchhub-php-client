<?php

// Функція для відкриття підключення до бази даних SQLite
function openDatabase() {
    $db = new SQLite3('connections.db');
    return $db;
}

// Функція для створення таблиці в базі даних SQLite, якщо вона ще не існує
function createTableIfNotExists($db) {
    $query = "CREATE TABLE IF NOT EXISTS connections (
                mistake TEXT PRIMARY KEY,
                correct TEXT
            )";
    $db->exec($query);
}

// Функція для вставки даних з файлу JSON до бази даних SQLite
function insertDataFromJSON($db) {
    /* Зчитуємо дані з файлу JSON
    $json = file_get_contents('base.json');
    $data = json_decode($json, true);

    // Вставляємо дані до таблиці
    foreach ($data['mappings'] as $mapping) {
        $mistake = $mapping['from'];
        $correct = $mapping['to'];
        $query = "INSERT INTO connections (mistake, correct) VALUES ('$mistake', '$correct')";
        $db->exec($query);
    }*/
}

// Відкриваємо підключення до бази даних SQLite
$db = openDatabase();

// Створюємо таблицю, якщо вона ще не існує
createTableIfNotExists($db);

// Вставляємо дані з файлу JSON до бази даних SQLite
insertDataFromJSON($db);

// Закриваємо підключення до бази даних SQLite
$db->close();

echo "Data inserted successfully.";

?>
