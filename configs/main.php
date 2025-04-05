<?php

namespace App\Configs;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;  
use Illuminate\Database\Capsule\Manager as Capsule;

trait CarbonTimeTrait { 
    public static function validateTime(int|string $startTime, int|string $endTime): bool {
        $startTime = Carbon::parse($startTime, APP_TIMEZONE);
        $endTime = Carbon::parse($endTime, APP_TIMEZONE);
        $now = Carbon::now(APP_TIMEZONE);
        
        return $now->isBetween($startTime, $endTime);
    }
    
    public static function validateExpiredTimeToNow(int|string $time): bool {
        $now = Carbon::now(APP_TIMEZONE);
        $time = Carbon::parse($time, APP_TIMEZONE);
        return $now->isBefore($time);
    }
                                                                            
    public static function currentTime(string $format = 'd_m_y_l_h_i_s', string $timezone = APP_TIMEZONE): string {
        date_default_timezone_set($timezone);
        return date($format);
    }

    public static function isoTimeString(string|int|null $timestamp = null, string $timezone = APP_TIMEZONE): string {
        if (is_null($timestamp)) {
            return Carbon::now($timezone)->toIso8601ZuluString();
        } elseif (is_int($timestamp)) {
            return Carbon::createFromTimestamp($timestamp)->tz($timezone)->toIso8601ZuluString();
        } else {
            return Carbon::parse($timestamp)->tz($timezone)->toIso8601ZuluString();
        }
    }
}

class Main {
    use CarbonTimeTrait;
    
    public const STATUS_CHECK = ['status' => true];
    public const STATUS_UNCHECK = ['status' => false];
        
    public static function manager(): Capsule {
        global $capsule;
        return $capsule;
    }

    public static function capsule(): string {
        return Capsule::class;
    }

    public static function getDBConfig(): array {
        return [
            'driver' => DB_DRIVER,
            'host' => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASS, 
            'database' => DB_NAME,
            'charset' => DB_CHARSET,
            'collation' => DB_COLLATION,
            'port' => DB_PORT,
            'prefix' => DB_PREFIX,
        ];
    }

    public static function writeLogFile(string $path, $message, bool $append = true, string $category = 'INFO'): string {
        if(empty($message)){
            return "No message to log";
        }

        if(is_array($message) || is_object($message)){
            $echo = json_encode($message, JSON_PRETTY_PRINT). PHP_EOL. PHP_EOL;
        } else {
            $echo = PHP_EOL . $message . PHP_EOL;
        }

        // if dir not exists then create it
        if(!is_dir(dirname($path))){
            mkdir(dirname($path), 0777, true);
        }

        try {
            if($append){
                $time = self::currentTime('d/m/Y - l - h:i:s');
                $text = "[{$time}] - {$category}: {$echo}\n\n";
                
                // open file (append mode)
                $handle = fopen($path, 'a');
                if ($handle === false) {
                    return "Error: Log file '{$path}' cannot be opened";
                }
                
                // write to file
                fwrite($handle, $text);
                
                // close file
                fclose($handle);
                
                return "Log has been saved, view here -> {$path}";
            }

            // for general writing (write mode)
            $handle = fopen($path, 'w');
            if ($handle === false) {
                return "Error: File '{$path}' cannot be opened";
            }
            
            // write to file
            fwrite($handle, $echo);
            
            // close file
            fclose($handle);
            
            return "Large contents are stored in the file -> {$path}";
        } catch(Exception $e){
            return "Error: " . $e->getMessage();
        }
    }
}

trait TimeTrait {
    public static function validateExpiredTimeToNow(int|string $time): bool {
        return self::validateTime('now', $time);
    }

    public static function validateTime(int|string $startTime, int|string $endTime): bool {
        if(is_int($startTime)) $startTime = self::isoTimeString($startTime);
        if(is_int($endTime)) $endTime = self::isoTimeString($endTime);
        
        echo "Start Time: {$startTime} \t";
        echo "End Time: {$endTime}" . PHP_EOL;

        echo "Start Time (Carbon): " . Carbon::parse($startTime)->format('Y-m-d H:i:s');
        echo "\rEnd Time (Carbon): " . Carbon::parse($endTime)->format('Y-m-d H:i:s') . PHP_EOL;

        $tz = new DateTimeZone(APP_TIMEZONE);
        $startTime = new DateTime($startTime);
        $endTime = new DateTime($endTime);

        echo $startTime->format('Y-m-d H:i:s') . ' < ' . $endTime->format('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;
        return $startTime < $endTime;
    }
    
    
    public static function currentTime(string $format = 'd_m_y_l_h_i_s', string $timezone = APP_TIMEZONE): string {
        date_default_timezone_set($timezone);
        return date($format);
    }

    public static function isoTimeString(string|int|null $timestamp = null, string $format = 'Y-m-d\TH:i:s\Z', string $timezone = APP_TIMEZONE): string {
        $tz = new DateTimeZone($timezone);
    
        if (is_string($timestamp)) {
            $dt = new DateTime($timestamp, $tz);
        } elseif (is_int($timestamp)) {
            $dt = (new DateTime('@' . $timestamp))->setTimezone($tz);
        } else {
            $dt = new DateTime('now', $tz);
        }
    
        return $dt->format($format);
    }  
    
    public static function makeTimeDiffForHumans(string $date): string {
        $date = new DateTime($date);
        $now = new DateTime();
        $diff = $now->diff($date);
        if($diff->y > 0) return $diff->y . ' years ago';
        if($diff->m > 0) return $diff->m . ' months ago';
        if($diff->d > 0) return $diff->d . ' days ago';
        if($diff->h > 0) return $diff->h . ' hours ago';
        return $diff->i . ' minutes ago';
    }

    public static function makeTimeDifference(
        int $duration, 
        ?string $startDate = null, 
        ?string $format = null, 
        ?string $unit = null, 
        ?string $timezone = null
    ): string {
        if(!$unit) $unit = 'seconds';
        if(!$format) $format = 'Y-m-d\TH:i:s\Z';
        if(!$timezone) $timezone = APP_TIMEZONE;
        if(!$startDate) $startDate = 'now';
    
        $timezone = new DateTimeZone($timezone);
    
        $startDate = new DateTime($startDate, $timezone);
        $expiry = clone $startDate;
        $startDate = $startDate->format($format);
    
        $expiry->modify("+{$duration} {$unit}");
        return $expiry->format($format);
    }
}

