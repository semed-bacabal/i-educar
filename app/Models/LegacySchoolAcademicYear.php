<?php

namespace App\Models;

use App\Models\Builders\LegacySchoolAcademicYearBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * LegacySchoolAcademicYear
 *
 * @property int $ano
 *
 * @method static LegacySchoolAcademicYearBuilder query()
 *
 * @property array<int, string> $fillable
 */
class LegacySchoolAcademicYear extends LegacyModel
{
    /** @use HasBuilder<LegacySchoolAcademicYearBuilder> */
    use HasBuilder;

    public const NOT_INITIALIZED = 0;

    public const IN_PROGRESS = 1;

    public const FINALIZED = 2;

    protected $table = 'pmieducar.escola_ano_letivo';

    /**
     * Builder dos filtros
     */
    protected static string $builder = LegacySchoolAcademicYearBuilder::class;

    protected $fillable = [
        'ref_cod_escola',
        'ano',
        'ref_usuario_cad',
        'ref_usuario_exc',
        'andamento',
        'data_exclusao',
        'ativo',
        'turmas_por_ano',
        'copia_dados_professor',
        'copia_dados_demais_servidores',
        'copia_turmas',
    ];

    protected function year(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->ano
        );
    }

    /**
     * @return BelongsTo<LegacySchool, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(LegacySchool::class, 'ref_cod_escola');
    }

    /**
     * @return HasMany<LegacyAcademicYearStage, $this>
     */
    public function academicYearStages(): HasMany
    {
        return $this->hasMany(LegacyAcademicYearStage::class, 'escola_ano_letivo_id');
    }
}
