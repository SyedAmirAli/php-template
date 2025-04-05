<?php

namespace App\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Configs\Main;
use App\Migrations\Schema;

final class RoleHasPermissionsMigration extends Migration {
    public const TABLE = 'role_has_permissions';

    public function up() {  
        Main::manager()->schema()->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('menu_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['role_id', 'menu_id', 'permission_id'], 'role_permission');
        });
    }

    public function down() {
        Main::manager()->schema()->dropIfExists(self::TABLE);
    }

    public function fresh() {
        return Schema::migrateFresh($this);
    }
}
