<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

return new class extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999806], [
            'parent_id' => Menu::query()->where('old', 999400)->firstOrFail()->getKey(),
            'process' => 999806,
            'title' => 'Atestado de abandono',
            'order' => 1,
            'parent_old' => 999400,
            'link' => '/module/Reports/AbandonmentCertificate'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999806)->delete();
    }
};
