<?php
namespace App\Configs;

use Exception;
use stdClass;
use PDO;
use PDOException;
use App\Configs\Main;
use App\Configs\Log;


class Connection extends Main {
    protected PDO $conn;

    public function __construct(array $dbConfig = []) {
        $configs = empty($dbConfig) ? self::getDBConfig() : $dbConfig;
        $connect = self::connect($configs);

        if($connect->error){
            Log::error($connect->message);
            throw new Exception($connect->message . PHP_EOL . json_encode($configs, 128) . PHP_EOL);  
        }

        $this->conn = $connect->conn;
    } 
    
    public static function connect(array $dbConfig = [
        'driver' => null,
        'host' => null,
        'user' => null,
        'pass' => null,
        'name' => null,
        'charset' => null,
        'collate' => null,
        'port' => null,
    ]): stdClass {
        if(!isset($dbConfig['driver']) || is_null($dbConfig['driver'])){
            $dbConfig['driver'] = DB_DRIVER; 
        }
        if(!isset($dbConfig['host']) || is_null($dbConfig['host'])){
            $dbConfig['host'] = DB_HOST; 
        }
        if(!isset($dbConfig['username']) || is_null($dbConfig['username'])){
            $dbConfig['username'] = DB_USER; 
        }
        if(!isset($dbConfig['password']) || is_null($dbConfig['password'])){
            $dbConfig['password'] = DB_PASS; 
        }
        if(!isset($dbConfig['database']) || is_null($dbConfig['database'])){
            $dbConfig['database'] = DB_NAME; 
        }
        if(!isset($dbConfig['charset']) || is_null($dbConfig['charset'])){
            $dbConfig['charset'] = DB_CHARSET; 
        }
        if(!isset($dbConfig['collation']) || is_null($dbConfig['collation'])){
            $dbConfig['collation'] = DB_COLLATION; 
        }
        if(!isset($dbConfig['port']) || is_null($dbConfig['port'])){
            $dbConfig['port'] = DB_PORT; 
        }

        $newConn = new stdClass();
        $newConn->error = false;
        $newConn->message = '';
        $newConn->conn = null;

        try {
            $pdo = new PDO(
                sprintf("%s:host=%s;port=%s;dbname=%s;charset=%s", 
                    $dbConfig['driver'], 
                    $dbConfig['host'], 
                    $dbConfig['port'], 
                    $dbConfig['database'], 
                    $dbConfig['charset']
                ), 
                $dbConfig['username'], 
                $dbConfig['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $newConn->conn = $pdo;
            $newConn->message = 'Connected successfully';

            return $newConn;
            // return $pdo;  
        } catch(PDOException $e){
            $newConn->error = true;
            $newConn->message = $e->getMessage();
            return $newConn;
        }
    }
}