<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999881], [
            'parent_id' => Menu::query()->where('old', 999450)->firstOrFail()->getKey(),
            'process' => 999881,
            'title' => 'Boletim de transferência',
            'parent_old' => 999450,
            'link' => '/module/Reports/ReportCardTransference'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('old', 999881)->delete();
    }
};
