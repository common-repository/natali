<?php

class Natali_Handler
{
    public static $status;
    public static $message;
    public static $trace;
    public static $traceAsString;
    public static $file;

    public static function error(string $event, Exception $error, $time = null, $data = []) {
        Natali_Log::set("$event - " .
                        $error->getMessage() .
                        $error->getTraceAsString(),
                        $error->getCode()
        );

        return [
            'event'    => "$event - Ошибка",
            'status'   => $error->getCode(),
            'message'  => $error->getMessage(),
            'file'     => $error->getFile(),
            'trace'    => $error->getTrace(),
            'line'     => $error->getLine(),
            'previous' => $error->getPrevious(),
            'time'     => $time
        ];
    }

    public static function success(string $event, $success = 'Успешно выполнен', $body = null, $time = null) {
        $response = [
            'status'  => 200,
            'event'   => $event,
            'message' => $success,
        ];

        if ($time) {
            $response['time'] = $time;
        }

        if (is_string($body)) {
            Natali_Log::set("$event - " . $success, 200);
            $response['body'] = $body;
            return $response;
        }

        if (is_array($body)) {
            Natali_Log::setArray($body, "$event - " . $success, 200);
            return array_merge($response, $body);
        }

        return $response;
    }
}