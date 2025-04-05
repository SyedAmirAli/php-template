<?php

namespace App\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Configs\Main;
use App\Migrations\Schema;


final class MigrationsMigration extends Migration
{
    public const TABLE = 'migrations';

    public function up()
    {
        Main::manager()->schema()->create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('migration');
            $table->string('table');
            $table->text('note')->nullable();
            $table->integer('batch')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Main::manager()->schema()->dropIfExists(self::TABLE);
    }

    public function fresh() {
        return Schema::migrateFresh($this);
    }
}   
