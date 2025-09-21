<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            'DROP FUNCTION IF EXISTS modules.frequencia_por_componente(cod_matricula_id integer, cod_disciplina_id integer, cod_turma_id integer);'
        );

        DB::unprepared(
            file_get_contents(__DIR__ . '/../../sqls/functions/modules.frequencia_por_componente_2023-02-13.sql')
        );
    }
};
