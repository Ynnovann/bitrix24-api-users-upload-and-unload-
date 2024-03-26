<?php
//Тестовое задание. Загрузка в "Битрикс24" (site2.ru) пользователей, полученных по адресу http://site1.ru/api/uploadUsers .
//Скрипт должен запускаться в час ночи с помощью cron и настройки "0 1 * * * php /upload_users_from_site1.php".

require 'vendor/autoload.php'; //Подключение библиотеки "Guzzle", установленной с помощью менеджера "Composer".

use GuzzleHttp\Client;

//Функция для загрузки пользователей на "Битрикс24".
function uploadUsersToBitrix24($accessToken, $users)
{
    $client = new Client();

    foreach ($users as $user) {
        $userId = $user['user_id'];
        $userName = $user['userName'];
        $userEmail = $user['userEmail'];

        //Проверка наличия пользователя в списке с конкретным ID.
        $response = $client->post("https://site2.ru/rest/user.get.json", [
            'form_params' => [
                'access_token' => $token,
                'FILTER' => ['ID' => $userId]
            ]
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!empty($responseData['result'])) {
            //Если пользователь найден в списке, то обновляем его данные.
            $response = $client->post("https://site2.ru/rest/user.update.json", [
                'form_params' => [
                    'access_token' => $token,
                    'ID' => $userId,
                    'NAME' => $userName,
                    'EMAIL' => $userEmail
                ]
            ]);
        } else {
            //Если пользователь в списке не найден, добавляем нового.
            $response = $client->post("https://site2.ru/rest/user.add.json", [
                'form_params' => [
                    'access_token' => $token,
                    'NAME' => $userName,
                    'EMAIL' => $userEmail
                ]
            ]);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (isset($responseData['error'])) {
            echo '<p>Внимание! Даные пользователя не загружены на site2.ru. Описание ошибки: '.$responseData['error_description'].'</p>';
        } else {
            echo '<p>Даные пользователя не загружены на site2.ru: '.$userName.'</p>';
        }
    }
}

//Для подключения к "Битрикс24".
$token = 'строка_токена_для_доступа';

$responseUsers = file_get_contents('http://site1.ru/api/uploadUsers');
$users = json_decode($response_users, true);

//Вызываем функцию загрузки пользователей, ранее объявленную. Массив данных пользовтаелей уже содержится в пеерменной $users .
uploadUsersToBitrix24($token, $users);
?>