<?php

namespace App\Models;

use App\Traits\Ativo;
use App\Traits\HasLegacyDates;
use App\Traits\HasLegacyUserAction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyRegistrationDisciplinaryOccurrenceType extends LegacyModel
{
    use Ativo;
    use HasLegacyDates;
    use HasLegacyUserAction;

    protected $table = 'pmieducar.matricula_ocorrencia_disciplinar';

    protected $primaryKey = 'cod_ocorrencia_disciplinar';

    protected $fillable = [
        'ref_cod_matricula',
        'ref_cod_tipo_ocorrencia_disciplinar',
        'sequencial',
        'ref_usuario_exc',
        'ref_usuario_cad',
        'observacao',
        'data_exclusao',
        'ativo',
        'visivel_pais',
        'updated_at',
    ];

    /**
     * @return BelongsTo<LegacyRegistration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(LegacyRegistration::class, 'ref_cod_matricula');
    }

    /**
     * @return BelongsTo<LegacyDisciplinaryOccurrenceType, $this>
     */
    public function disciplinarOccurrenceType(): BelongsTo
    {
        return $this->belongsTo(LegacyDisciplinaryOccurrenceType::class, 'ref_cod_tipo_ocorrencia_disciplinar');
    }
}
