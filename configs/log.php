<?php

namespace App\Configs;

use Exception;
use App\Configs\Main;

class Log extends Main
{
    public static function putIntoJsonLog($message, bool $append = false): string {
        return self::writeLogFile(JSON_LOG_PATH, $message, $append);
    }

    private static function log($message, string $category, bool $append = true): string {
        return self::writeLogFile(LOG_PATH, $message, $append, $category);
    }

    public static function info($message, bool $append = true): string {
        return self::log($message, 'INFO', $append);
    }

    public static function error($message, bool $append = true): string {
        return self::log($message, 'ERROR', $append);
    }

    public static function warning($message, bool $append = true): string {
        return self::log($message, 'WARNING', $append);  
    }

    public static function debug(...$messages): string {
        $append = true;
        $lastMessage = end($messages); 
        if(is_bool($lastMessage)) {
            $append = $lastMessage;
            unset($messages[count($messages) - 1]);
        }
        return self::log($messages, 'DEBUG', $append);
    }
}