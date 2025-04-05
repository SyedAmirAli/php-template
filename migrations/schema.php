<?php

namespace App\Migrations;

use stdClass;
use PDOException;
use App\Configs\Log;
use App\Configs\Connection;

use App\Migrations\{
    MigrationsMigration,
    UsersMigration,
    MenusMigration,
    UserRolesMigration,
    RolesMigration,
    PermissionsMigration,
    RoleHasPermissionsMigration
};
 
interface SchemaInterface {
    public static function registerMigration(
        object $context, 
        string $type, 
        bool $force
    ): string;

    public static function migrateFresh(
        object $context
    ): bool;
}

class Schema extends Connection implements SchemaInterface {
    public const CREATE = 1;
    public const DROP = 2;
    public const UPDATE = 3;
    public const ALTER = 4;
    public const TRUNCATE = 5;
    public const COMMENT = 6;
    public const INDEX = 7;
    public const FOREIGN = 8;
    public const UNIQUE = 9;
    public const PRIMARY = 10;
    public const CHECK = 11;
    public const DEFAULT = 12;
    public const ENGINE = 13;
    public const FRESH = 14;
    
    public function __construct() {
        parent::__construct();
    }

    // Migration of users table
    public static function migrateUsers(string $type = 'up', bool $force = false): string {
        return self::registerMigration(new UsersMigration(), $type, $force);
    }

    // Migration of menus table
    public static function migrateMenus(string $type = 'up', bool $force = false): string {
        return self::registerMigration(new MenusMigration(), $type, $force);
    }

    // Migration of roles table
    public static function migrateRoles(string $type = 'up', bool $force = false): string {
        return self::registerMigration(new RolesMigration(), $type, $force);
    }

    // Migration of user roles table
    public static function migrateUserRoles(string $type = 'up', bool $force = false): string {
        return self::registerMigration(new UserRolesMigration(), $type, $force);
    }   

    // Migration of role has permissions table
    public static function migrateRoleHasPermissions(string $type = 'up', bool $force = false): string {
        return self::registerMigration(new RoleHasPermissionsMigration(), $type, $force);
    }   

    // Migration of permissions table
    public static function migratePermissions(string $type = 'up', bool $force = false): string {
        return self::registerMigration(new PermissionsMigration(), $type, $force);
    }

    // Migration of blacklists table
    public static function migrateBlacklists(string $type = 'up', bool $force = false): string {
        return self::registerMigration(new BlacklistsMigration(), $type, $force);
    }

    // Migration of migrations table
    public static function migrateMigrations(string $type = 'up'): string {
        $migration = new MigrationsMigration();

        if($type === 'up') {
            $migration->up(); 
            return 'Successfully migrated migrations table.';
        }

        if($type === 'down') {
            $migration->down();
            return 'Successfully migrated migrations table.';
        }

        if($type === 'fresh') {
            if($migration->fresh()) return 'Successfully migrated fresh migrations table.';
            return 'Failed to migrate fresh migrations table.';
        }

        return 'Invalid migration type.';
    }

    public static function registerMigration(object $context, string $type, bool $force/* , ?string $extraMethod = null */): string {
        try {
            $table = $context::TABLE;
            $migration = new $context();

            if($type === 'up') {
                $result = self::recordTableInMigration($table, self::CREATE, $force);
                if($result->status) $migration->up(); 
                return $result->message;
            }

            if($type === 'down') {
                $migration->down();
                $result = self::removeTableFromMigration($table);
                return $result->message;
            }

            if($type === 'fresh') {
                if($migration->fresh()) return "Successfully migrated fresh {$table} table.";
                return "Failed to migrate fresh {$table} table.";
            }

            if(method_exists($migration, $type)) {
                $migration->$type();
                return "Successfully {$type} {$table} table.";
            }

            return 'Invalid migration type.';
        } catch(PDOException $e) {
            Log::error("Error migrating: " . $e->getMessage());
            return "Failed to migrate.";
        }
    }

    public static function recordTableInMigration(
        string $table, 
        int $type = 1, 
        bool $force = false, 
        int $batch = 0, 
        ?string $note = null
    ): stdClass {
        $connect = self::connect();
        $result = new stdClass();
        $result->status = false;
        $result->message = '';

        if($connect->error) {
            $result->status = false;
            $result->message = "Failed to connect to database: " . $connect->message;
            Log::error($result->message);
            return $result;
        }

        $migration = "";
        $pdo = $connect->conn;
        
        switch($type) {
            case self::CREATE:
                $migration = "create_{$table}_table";
                break;
            case self::DROP:
                $migration = "drop_{$table}_table";
                break;
            case self::UPDATE:
                $migration = "update_{$table}_table";
                break;
            case self::ALTER:
                $migration = "alter_{$table}_table";
                break;  
            case self::TRUNCATE:
                $migration = "truncate_{$table}_table";
                break;
            case self::COMMENT:
                $migration = "comment_{$table}_table";
                break;  
            case self::INDEX:
                $migration = "index_{$table}_table";
                break;
            case self::FOREIGN:
                $migration = "foreign_{$table}_table";
                break;  
            case self::UNIQUE:
                $migration = "unique_{$table}_table";
                break;
            case self::PRIMARY:
                $migration = "primary_{$table}_table";
                break;
            case self::CHECK:
                $migration = "check_{$table}_table";
                break;
            case self::DEFAULT:
                $migration = "default_{$table}_table";
                break;  
            case self::ENGINE:
                $migration = "engine_{$table}_table";
                break;
            case self::FRESH:
                $migration = "fresh_{$table}_table";
                break;
            default:
                $migration = "{$table}_table";
                break;

        }

        if(!$batch) $batch = $type;

        if(self::tableExistsInMigration($table)) {
            if($force) {
                $migration = "force_remigrate_to_{$migration}";
            } else {
                $result->status = false;
                $result->message = "Table {$table} already exists in migrations table.";
                Log::error($result->message);
                return $result;
            }
        }

        $sql = "INSERT INTO migrations (`migration`, `batch`, `table`, `note`) VALUES (:migration, :batch, :table_name, :note)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':migration', $migration);
        $stmt->bindParam(':batch', $batch);
        $stmt->bindParam(':table_name', $table);
        $stmt->bindParam(':note', $note);

        if ($stmt->execute()) {
            $result->status = true;
            $result->message = "Successfully inserted migration record for table {$table}.";
            Log::info($result->message);
            return $result;
        }

        $result->status = false;
        $result->message = "Failed to insert migration record for table {$table}.";
        Log::error($result->message);
        return $result;
        
    }

    private static function tableExistsInMigration($table)
    {
        $sql = "SELECT COUNT(*) FROM migrations WHERE `table` = :table";
        $stmt = self::connect()->conn->prepare($sql);
        $stmt->bindParam(':table', $table);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    public static function migrateFresh(object $context): bool {
        if(!is_object($context) || !method_exists($context, 'down') || !method_exists($context, 'up')) {
            Log::error("Invalid context provided. Context must be an object with down() and up() methods.");
            return false;
        }

        try {
            $table = $context::TABLE;
            
            $context->down();
            $removeResult = self::removeTableFromMigration($table);
            
            if (!$removeResult->status) {
                Log::warning("Failed to remove migration record, but continuing: " . $removeResult->message);
            }
            
            $context->up();
            $result = self::recordTableInMigration($table, self::FRESH, true);
            
            return true;
        } catch(PDOException $e) {
            $message = "Error refreshing table: " . $e->getMessage();
            Log::error($message);
            return false;
        }
    }

    private static function removeTableFromMigration(string $table): stdClass {
        $connect = self::connect();
        $result = new stdClass();
        $result->status = false;
        $result->message = '';

        if($connect->error) {
            $result->status = false;
            $result->message = "Failed to connect to database: " . $connect->message;
            Log::error($result->message);
            return $result;
        }

        $pdo = $connect->conn;
        
        $sql = "DELETE FROM migrations WHERE `table` = :table";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':table', $table);

        if ($stmt->execute()) {
            $result->status = true;
            $result->message = "Successfully removed migration record for table {$table}.";
            Log::info($result->message);
            return $result;
        }

        $result->status = false;
        $result->message = "Failed to remove migration record for table {$table}.";
        Log::error($result->message);
        return $result;
    }
}