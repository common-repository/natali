<?php

class Natali_Api_DataBase
{
    protected const BASE_URL = 'https://natali37.ru/api/';

    /**
     * @throws ErrorException
     */
    public function get($urlPath) {
        $url = self::BASE_URL . $urlPath;
        $response = wp_remote_get($url); //Делаем запрос к API натали

        if (is_null($response)) {
            throw new ErrorException('Error: Не удалось соединиться с сайтом natali', 400);
        } else {
            return $this->dataDecode($response);
        }
    }

    private function dataDecode($data) {
        $dataEncoded = wp_remote_retrieve_body($data);           // Убираем из запроса все кроме тела
        return json_decode($dataEncoded, true); // Преобразуем JSON ответ в PHP переменную
    }
}
