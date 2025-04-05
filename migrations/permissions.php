<?php

namespace App\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Configs\Main;
use App\Migrations\Schema;

final class PermissionsMigration extends Migration {
    public const TABLE = 'permissions';

    public function up() {  
        Main::manager()->schema()->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            // Add index for better performance
            $table->index('role_id');
        });
    }

    public function down() {
        Main::manager()->schema()->dropIfExists(self::TABLE);
    }

    public function fresh() {
        return Schema::migrateFresh($this);
    }
}



