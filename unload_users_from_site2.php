<?php
//Тестовое задание. Передача из "Битрикс24" (site2.ru) пользователей по адресу https://site1.ru/api/syncUsers .
//Скрипт должен запускаться в час ночи с помощью cron и настройки "0 1 * * * php /unload_users_from_site2.php".

require 'vendor/autoload.php'; //Подключение библиотеки "Guzzle", установленной с помощью менеджера "Composer".

// Получение списка пользователей из Bitrix24 (site2.ru).
$token = 'строка_токена_для_доступа';

$users = [];

$response = file_get_contents("https://site2.ru/rest/user.get.json?auth=$token");
if ($response) {
    $usersData = json_decode($response, true);
    if (!empty($usersData['result'])) {
        foreach ($usersData['result'] as $user) {
            $users[] = [
                'user_id' => $user['user_id'],
                'NAME' => $user['userName'],
                'EMAIL' => $user['userEmail']
            ];
        }
    }
}

$client = new GuzzleHttp\Client();

foreach ($users as $user) {
    $response = $client->post('https://site1.ru/api/syncUsers', [
        'headers' => [
            'Authorization' => 'Bearer строка_токена_для_доступа_к_сайту_site1.ru',
            'Content-Type' => 'application/json'
        ],
        'json' => [
            'user_id' => $user['user_id'],
            'NAME' => $user['userName'],
            'EMAIL' => $user['userEmail']
        ]
    ]);

    $responseData = json_decode($response->getBody()->getContents(), true);

    if (isset($responseData['success'])) {
        echo '<p>Данные о пользователе '.$user['userName'].'переданы на сайт site1.ru .</p>';                        
    } else {
        echo '<p>Внимание! Данные о пользователе '.$user['userName'].'не переданы на сайт site1.ru . Получена ошибка: '.$responseData['error'].'</p>';
    }
}
?>