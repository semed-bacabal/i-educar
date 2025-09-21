<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

return new class extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999828], [
            'parent_id' => Menu::query()->where('old', 999827)->firstOrFail()->getKey(),
            'process' => 999828,
            'title' => 'Auditoria',
            'parent_old' => 999827,
            'link' => '/module/Reports/AuditEvaluationsFaults'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999828)->delete();
    }
};
