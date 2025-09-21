<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyValueRoundingTable extends Model
{
    /**
     * @var string
     */
    protected $table = 'modules.tabela_arredondamento_valor';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tabela_arredondamento_id',
        'nome',
        'descricao',
        'valor_minimo',
        'valor_maximo',
        'casa_decimal_exata',
        'acao',
        'observacao',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'valor_minimo' => 'float',
        'valor_maximo' => 'float',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return BelongsTo<LegacyRoundingTable, $this>
     */
    public function roundingTable(): BelongsTo
    {
        return $this->belongsTo(LegacyRoundingTable::class, 'tabela_arredondamento_id');
    }
}
