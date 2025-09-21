<?php

use App\Support\Database\MigrationUtils;
use Illuminate\Database\Migrations\Migration;

class AddDataCancelInViewExporterStudent extends Migration
{
    use MigrationUtils;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->dropView('public.exporter_social_assistance');
        $this->dropView('public.exporter_student');

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_student-2021-07-19.sql')
        );

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_social_assistance-2020-05-07.sql')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropView('public.exporter_social_assistance');
        $this->dropView('public.exporter_student');

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_student-2021-04-19.sql')
        );

        $this->executeSqlFile(
            database_path('sqls/views/public.exporter_social_assistance-2020-05-07.sql')
        );
    }
}
