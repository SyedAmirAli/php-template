<?php

namespace App\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Configs\Main;
use PDOException;
use App\Configs\Log;
use App\Migrations\Schema;

final class MenusMigration extends Migration {
    public const TABLE = 'menus';

    public function up() {
        Main::manager()->schema()->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('href');
            $table->string('route')->nullable();
            $table->text('icon')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('order')->default(1);
            $table->enum('type', ['aran_properties', 'future_connect']);
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Main::manager()->schema()->dropIfExists(self::TABLE);
    }

    public function fresh() {
        return Schema::migrateFresh($this);
    }
}



