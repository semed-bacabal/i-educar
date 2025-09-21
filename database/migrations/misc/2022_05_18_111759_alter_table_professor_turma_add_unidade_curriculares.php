<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE IF EXISTS modules.professor_turma ADD COLUMN unidades_curriculares smallint[];');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'modules.professor_turma',
            static fn (Blueprint $table) => $table
                ->dropColumn('unidades_curriculares')
        );
    }
};
