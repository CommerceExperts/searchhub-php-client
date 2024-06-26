<?php

require_once 'vendor/autoload.php';

use SearchHub\Client\Config;
use SearchHub\Client\SearchHubClient;


////$config = new Config( "test", "working", "qa", "saas", null, getenv('SH_API_KEY'));
////
////
//////$test = array ("vinil click", "sichtschuztzäune", "klick-vinyl", "aboba", "sichtschutz zaune",
//////    "außen wand leuchte", "waschbecken- unterschrank", "feder nut bretter", "kette säge", "außenleuchten mit bewegungsmelder");
////
////$test = array ("\"vinil click\"", "\"sichtschuztzäune\\", "\\klick-vinyl", "\"aboba\\", "\"feder. nut bretter\"", "Cola \"Coca\"", "123", "finylböden", "wandaussenleuchten", "waschbecken mit untershrank");
////$number = 1;
////
////$numberOfQueries = $number * count($test);
////
////echo"\tSaaS mapper:";
////$start = microtime(true);
////$client = new SearchHubClient($config);
////
////for($i = 1; $i <= $number; $i++){
////    foreach ($test as $query)
////    {
////        $mappedQuery = $client->mapQuery($query);
////        //echo"$query -> $mappedQuery->masterQuery\n";
////        //echo json_encode($query);
////    }
////}
////
////$executionTime = microtime(true) -  $start;
////
////echo "\t\t$numberOfQueries query:\n" . "Total time: " . $executionTime . "s\nAverage time: " . $executionTime/$numberOfQueries . "s";
////
////
////echo"\n\n\n\n\tLocal mapper:";
////$config = new Config( "test", "working", "qa", "local", null, getenv('SH_API_KEY'));
////$start = microtime(true);
////
////$client = new SearchHubClient($config);
////
////for($i = 1; $i <= $number; $i++){
////    foreach ($test as $query)
////    {
////        $mappedQuery = $client->mapQuery($query);
////        //echo"$query -> $mappedQuery->masterQuery\n";
////    }
////}
////
////$executionTime = microtime(true) -  $start;
////
////echo "\t\t$numberOfQueries query:\n" . "Total time: " . $executionTime . "s\nAverage time: " . $executionTime/$numberOfQueries . "s";
////
////
//$testFile = 'C:\\Users\\Vitalii\\AppData\\Local\\Temp\\test.txt';
//
//try {
//    // Створення нового файлу для тестування
//    $fileHandle = fopen($testFile, 'w');
//    if ($fileHandle) {
//        fwrite($fileHandle, 'Перевірка доступу: запис успішний.');
//        fclose($fileHandle);
//        echo 'Тестовий файл створено і записано успішно.';
//    } else {
//        throw new Exception('Не вдалося створити файл.');
//    }
//
//    // Перевірка можливості читання файлу
//    $fileHandle = fopen($testFile, 'r');
//    if ($fileHandle) {
//        $content = fread($fileHandle, filesize($testFile));
//        fclose($fileHandle);
//        echo 'Тестовий файл прочитано успішно: ' . $content;
//    } else {
//        throw new Exception('Не вдалося відкрити файл для читання.');
//    }
//
//    // Видалення тестового файлу після перевірки
//    unlink($testFile);
//} catch (Exception $e) {
//    die('Помилка при перевірці прав доступу: ' . $e->getMessage());
//}

$config = new Config( "test", "working", "qa", "saas", null, getenv('SH_API_KEY'));

echo sys_get_temp_dir() . "\\" . "SearchHub.{$config->getAccountName()}.{$config->getChannelName()}.{$config->getStage()}.database.sqlite";