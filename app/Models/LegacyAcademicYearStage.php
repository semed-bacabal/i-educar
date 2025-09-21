<?php

namespace App\Models;

use App\Models\Builders\LegacyAcademicYearStageBuilder;
use App\Support\Database\DateSerializer;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyAcademicYearStage extends LegacyModel
{
    use DateSerializer;

    /** @use HasBuilder<LegacyAcademicYearStageBuilder> */
    use HasBuilder;

    protected $table = 'pmieducar.ano_letivo_modulo';

    /**
     * Builder dos filtros
     */
    protected static string $builder = LegacyAcademicYearStageBuilder::class;

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    protected $fillable = [
        'ref_ano',
        'ref_ref_cod_escola',
        'sequencial',
        'ref_cod_modulo',
        'data_inicio',
        'data_fim',
        'dias_letivos',
        'escola_ano_letivo_id',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo<LegacyStageType, $this>
     */
    public function stageType(): BelongsTo
    {
        return $this->belongsTo(LegacyStageType::class, 'ref_cod_modulo');
    }

    /**
     * @return BelongsTo<LegacySchoolAcademicYear, $this>
     */
    public function schoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(LegacySchoolAcademicYear::class, 'ref_ref_cod_escola', 'ref_cod_escola');
    }
}
