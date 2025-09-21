<?php

namespace App\Models;

use App\Models\Concerns\SoftDeletes\LegacySoftDeletes;
use App\Traits\HasInstitution;
use App\Traits\HasLegacyDates;
use App\Traits\HasLegacyUserAction;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array<int, string> $fillable
 */
class WithdrawalReason extends LegacyModel
{
    use HasInstitution;
    use HasLegacyDates;
    use HasLegacyUserAction;
    use LegacySoftDeletes;

    protected $table = 'pmieducar.motivo_afastamento';

    protected $primaryKey = 'cod_motivo_afastamento';

    protected $fillable = [
        'nm_motivo',
        'descricao',
    ];

    /**
     * @var array<string, string>
     */
    public array $legacy = [
        'id' => 'cod_motivo_afastamento',
        'name' => 'nm_motivo',
        'description' => 'descricao',
    ];

    /**
     * @return HasMany<EmployeeWithdrawal, $this>
     */
    public function employeeWithdrawals(): HasMany
    {
        return $this->hasMany(EmployeeWithdrawal::class, 'ref_cod_motivo_afastamento');
    }
}
