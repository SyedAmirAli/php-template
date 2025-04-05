<?php

namespace App\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Configs\Main;
use App\Migrations\Schema;

final class RolesMigration extends Migration {
    public const TABLE = 'roles';

    public function up() {
        Main::manager()->schema()->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down() {
        Main::manager()->schema()->dropIfExists(self::TABLE);
    }

    public function fresh() {
        return Schema::migrateFresh($this);
    }
}
