<?php

namespace App\Models\Exporter;

use App\Models\Exporter\Builders\EnrollmentEloquentBuilder;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Stage extends Model
{
    /** @use HasBuilder<EnrollmentEloquentBuilder> */
    use HasBuilder;

    protected static string $builder = EnrollmentEloquentBuilder::class;

    /**
     * @var string
     */
    protected $table = 'exporter_stages';

    /**
     * @var Collection<string, string>
     */
    protected $alias;

    /**
     * @return array
     */
    public function getExportedColumnsByGroup()
    {
        return [
            'Etapas' => [
                'school_name' => 'Escola',
                'school_class' => 'Turma',
                'stage_name' => 'Tipo de etapa',
                'stage_number' => 'Etapa',
                'stage_start_date' => 'Data início',
                'stage_end_date' => 'Data fim',
                'stage_days' => 'Dias letivos',
                'stage_type' => 'Padrão/Turma',
                'posted_data' => 'Possui lançamentos',
            ],
        ];
    }

    public function getLabel(): string
    {
        return 'Calendário letivo';
    }

    public function getDescription(): string
    {
        return 'Exportação de todos os calendários letivos do ano filtrado para identificação das datas de início e fim das etapas e existência de lançamentos.';
    }

    /**
     * @param string $column
     * @return string
     */
    public function alias($column)
    {
        /** @phpstan-ignore-next-line */
        if (empty($this->alias)) {
            /** @phpstan-ignore-next-line */
            $this->alias = collect($this->getExportedColumnsByGroup())->flatMap(function ($item) {
                return $item;
            });
        }

        return $this->alias->get($column, $column);
    }
}
