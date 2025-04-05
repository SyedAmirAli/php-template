<?php

namespace App\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Configs\Main;
use App\Migrations\Schema;

final class UserRolesMigration extends Migration {
    public const TABLE = 'user_roles';

    public function up() {
        Main::manager()->schema()->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();
            
            // Add unique constraint
            $table->unique(['user_id', 'role_id'], 'user_role');
        });
    }

    public function down() {
        Main::manager()->schema()->dropIfExists(self::TABLE);
    }

    public function fresh() {
        return Schema::migrateFresh($this);
    }
}


