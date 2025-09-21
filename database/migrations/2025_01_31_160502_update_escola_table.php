<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmieducar.escola', function (Blueprint $table) {
            $table->smallInteger('característica_escolar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pmieducar.escola', function (Blueprint $table) {
            $table->dropColumn('característica_escolar');
        });
    }
};
