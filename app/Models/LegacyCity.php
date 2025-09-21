<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @deprecated Usar novo módulo de endereço
 *
 * @property string $nome
 * @property string $sigla_uf
 */
class LegacyCity extends Model
{
    protected $table = 'public.municipio';

    protected $primaryKey = 'idmun';

    protected $fillable = [
        'idmun',
        'nome',
        'sigla_uf',
        'area_km2',
        'idmreg',
        'idasmun',
        'cod_ibge',
        'geom',
        'tipo',
        'idmun_pai',
        'idpes_rev',
        'idpes_cad',
        'data_rev',
        'data_cad',
        'origem_gravacao',
        'operacao',
        'nome_limpo',
    ];

    public $timestamps = false;

    /**
     * {@inheritDoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->origem_gravacao = 'M';
            $model->data_cad = now();
            $model->operacao = 'I';
        });
    }

    /**
     * @return HasMany<LegacyDistrict, $this>
     */
    public function districts()
    {
        return $this->hasMany(LegacyDistrict::class, 'idmun', 'idmun');
    }

    protected function nameWithState(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nome . '-' . $this->sigla_uf
        );
    }
}
