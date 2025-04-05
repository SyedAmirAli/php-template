<?php

namespace App\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Configs\Main;
use App\Migrations\Schema;

final class BlacklistsMigration extends Migration {
    public const TABLE = 'blacklists';

    public function up() {
        Main::manager()->schema()->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('Bearer');
            $table->string('key')->default('token');
            $table->text('value');
            $table->string('expires_at');
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

    public function alterReasonColumn() {
        Main::manager()->schema()->table(self::TABLE, function (Blueprint $table) {
            $table->string('reason')->default('logout');
        });
    }
}



