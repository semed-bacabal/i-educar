<?php

namespace App\Models;

use App\Models\Builders\LegacySchoolClassBuilder;
use App\Models\Enums\DayOfWeek;
use App\Models\View\Discipline;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * LegacySchoolClass
 *
 * @property int                $id
 * @property int                $cod_turma
 * @property string             $name
 * @property string             $nm_turma
 * @property int                $year
 * @property int                $ano
 * @property int                $school_id
 * @property int                $ref_ref_cod_escola
 * @property int                $course_id
 * @property int                $ref_cod_curso
 * @property int                $grade_id
 * @property int                $ref_ref_cod_serie
 * @property int                $vacancies
 * @property int                $max_aluno
 * @property int                $ref_cod_disciplina_dispensada
 * @property bool               $multiseriada
 * @property bool               $visivel
 * @property int                $exempted_discipline_id
 * @property Carbon             $begin_academic_year
 * @property Carbon             $end_academic_year
 * @property LegacyCourse       $course
 * @property LegacyGrade        $grade
 * @property LegacySchool       $school
 * @property LegacySchoolGrade  $schoolGrade
 * @property LegacyEnrollment[] $enrollments
 * @property array<int, string> $fillable
 * @property string $hora_inicial
 * @property string $hora_final
 * @property LegacySchoolClassGrade[] $multigrades
 * @property LegacyAcademicYearStage[] $academicYearStages
 * @property LegacySchoolClassStage[] $schoolClassStages
 *
 * @method static LegacySchoolClassBuilder query()
 */
class LegacySchoolClass extends Model
{
    /** @use HasBuilder<LegacySchoolClassBuilder> */
    use HasBuilder;

    protected $table = 'pmieducar.turma';

    public const CREATED_AT = 'data_cadastro';

    protected $primaryKey = 'cod_turma';

    /**
     * Builder dos filtros
     */
    protected static string $builder = LegacySchoolClassBuilder::class;

    /**
     * Atributos legados para serem usados nas queries
     *
     * @var array<string, string>
     */
    public array $legacy = [
        'id' => 'cod_turma',
        'name' => 'nm_turma',
        'year' => 'ano',
    ];

    protected $fillable = [
        'ref_usuario_exc',
        'ref_usuario_cad',
        'ref_ref_cod_serie',
        'ref_ref_cod_escola',
        'nm_turma',
        'sgl_turma',
        'max_aluno',
        'multiseriada',
        'ativo',
        'ref_cod_turma_tipo',
        'hora_inicial',
        'hora_final',
        'hora_inicio_intervalo',
        'hora_fim_intervalo',
        'ref_cod_regente',
        'ref_cod_instituicao_regente',
        'ref_cod_instituicao',
        'ref_cod_curso',
        'ref_ref_cod_serie_mult',
        'ref_ref_cod_escola_mult',
        'visivel',
        'tipo_boletim',
        'turma_turno_id',
        'ano',
        'tipo_atendimento',
        'turma_mais_educacao',
        'atividade_complementar_1',
        'atividade_complementar_2',
        'atividade_complementar_3',
        'atividade_complementar_4',
        'atividade_complementar_5',
        'atividade_complementar_6',
        'aee_braille',
        'aee_recurso_optico',
        'aee_estrategia_desenvolvimento',
        'aee_tecnica_mobilidade',
        'aee_libras',
        'aee_caa',
        'aee_curricular',
        'aee_soroban',
        'aee_informatica',
        'aee_lingua_escrita',
        'aee_autonomia',
        'cod_curso_profissional',
        'etapa_educacenso',
        'ref_cod_disciplina_dispensada',
        'parecer_1_etapa',
        'parecer_2_etapa',
        'parecer_3_etapa',
        'parecer_4_etapa',
        'nao_informar_educacenso',
        'tipo_mediacao_didatico_pedagogico',
        'tipo_boletim_diferenciado',
        'dias_semana',
        'atividades_complementares',
        'atividades_aee',
        'local_funcionamento_diferenciado',
        'organizacao_curricular',
        'formas_organizacao_turma',
        'classe_com_lingua_brasileira_sinais',
        'hora_inicial_matutino',
        'hora_inicio_intervalo_matutino',
        'hora_fim_intervalo_matutino',
        'hora_final_matutino',
        'hora_inicial_vespertino',
        'hora_inicio_intervalo_vespertino',
        'hora_fim_intervalo_vespertino',
        'hora_final_vespertino',
        'etapa_agregada',
        'classe_especial',
        'formacao_alternancia',
        'area_itinerario',
        'tipo_curso_intinerario',
        'cod_curso_profissional_intinerario',
    ];

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cod_turma,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->year)) {
                    return $this->nm_turma;
                }

                return $this->nm_turma . ' (' . $this->year . ')';
            },
        );
    }

    protected function year(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->ano,
        );
    }

    protected function startTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hora_inicial ? Carbon::createFromFormat('H:i:s', $this->hora_inicial) : null
        );
    }

    protected function schoolId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->ref_ref_cod_escola,
        );
    }

    protected function courseId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->ref_cod_curso,
        );
    }

    protected function gradeId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->ref_ref_cod_serie,
        );
    }

    protected function visible(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->visivel,
        );
    }

    protected function daysOfWeekName(): Attribute
    {
        return Attribute::make(
            get: function () {
                $diasSemana = $this->dias_semana ?? [];
                $diff = array_diff([2, 3, 4, 5, 6], $diasSemana);

                if (count($diff) === 0) {
                    return 'Seg à Sex';
                }

                $daysOfWeek = array_map(function ($day) {
                    return DayOfWeek::tryFrom((int) $day)?->shortName();
                }, $diasSemana);

                return implode(', ', $daysOfWeek);
            },
        );
    }

    protected function exemptedDisciplineId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->ref_cod_disciplina_dispensada,
        );
    }

    protected function vacancies(): Attribute
    {
        return Attribute::make(
            get: function () {
                $vacancies = $this->max_aluno - $this->getTotalEnrolled();

                return max($vacancies, 0);
            },
        );
    }

    /**
     * Retorna o total de alunos enturmados desconsiderando matrículas de
     * dependência.
     */
    public function getTotalEnrolled(): int
    {
        return $this->enrollments()
            ->where('ativo', 1)
            ->whereHas('registration', function ($query) {
                $query->where('dependencia', false);
            })->count();
    }

    protected function beginAcademicYear(): Attribute
    {
        return Attribute::make(
            get: function () {
                // @phpstan-ignore-next-line
                if (!$this->course?->is_standard_calendar && ($schoolClassStages = $this->schoolClassStages()->orderBy('sequencial')->value('data_inicio'))) {
                    return $schoolClassStages;
                }

                return $this->academicYearStages()->orderBy('sequencial')->value('data_inicio');
            },
        );
    }

    protected function multiseries(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->multiseriada === 1,
        );
    }

    protected function endAcademicYear(): Attribute
    {
        return Attribute::make(
            get: function () {
                // @phpstan-ignore-next-line
                if (!$this->course?->is_standard_calendar && ($schoolClassStages = $this->schoolClassStages()->orderByDesc('sequencial')->value('data_fim'))) {
                    return $schoolClassStages;
                }

                return $this->academicYearStages()->orderByDesc('sequencial')->value('data_fim');
            },
        );
    }

    /**
     * Séries
     *
     * @return BelongsToMany<LegacyGrade, $this>
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(LegacyGrade::class, 'pmieducar.turma_serie', 'turma_id', 'serie_id');
    }

    /**
     * Anos Letivos
     *
     * @return HasMany<LegacySchoolAcademicYear, $this>
     */
    public function academicYears(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(LegacySchoolAcademicYear::class, 'ref_cod_escola', 'ref_ref_cod_escola')->whereColumn('escola_ano_letivo.ano', 'ano');
    }

    /**
     * @return BelongsTo<LegacyCourse, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LegacyCourse::class, 'ref_cod_curso');
    }

    /**
     * @return BelongsTo<LegacyGrade, $this>
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(LegacyGrade::class, 'ref_ref_cod_serie');
    }

    /**
     * Relacionamento com a escola.
     *
     * @return BelongsTo<LegacySchool, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(LegacySchool::class, 'ref_ref_cod_escola');
    }

    /**
     * @return BelongsTo<LegacyPerson, $this>
     */
    public function regentPerson(): BelongsTo
    {
        return $this->belongsTo(LegacyPerson::class, 'ref_cod_regente');
    }

    /**
     * Relacionamento com as enturmações.
     *
     * @return HasMany<LegacyEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(LegacyEnrollment::class, 'ref_cod_turma', 'cod_turma');
    }

    /**
     * Relacionamento com professor.
     *
     * @return HasMany<LegacySchoolClassTeacher, $this>
     */
    public function schoolClassTeachers(): HasMany
    {
        return $this->hasMany(LegacySchoolClassTeacher::class, 'turma_id');
    }

    /**
     * @return HasMany<LegacyAcademicYearStage, $this>|HasMany<LegacySchoolClassStage, $this>
     */
    public function stages(): HasMany
    {
        // @phpstan-ignore-next-line
        if ($this->course?->is_standard_calendar) {
            return $this->academicYearStages();
        }

        return $this->schoolClassStages();
    }

    /**
     * @return HasMany<LegacyAcademicYearStage, $this>
     */
    public function academicYearStages(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(LegacyAcademicYearStage::class, 'ref_ref_cod_escola', 'ref_ref_cod_escola')
            ->where('ref_ano', $this->year);
    }

    public function getStages(bool $standardCalendar): Collection // @phpstan-ignore-line
    {
        if ($standardCalendar) {
            $stages = $this->academicYearStages;
        } else {
            $stages = $this->schoolClassStages;
        }

        // @phpstan-ignore-next-line
        return $stages ?? collect();
    }

    /**
     * @return HasMany<LegacySchoolClassStage, $this>
     */
    public function schoolClassStages(): HasMany
    {
        return $this->hasMany(LegacySchoolClassStage::class, 'ref_cod_turma', 'cod_turma');
    }

    /**
     * @return HasMany<LegacySchoolClassGrade, $this>
     */
    public function multigrades(): HasMany
    {
        return $this->hasMany(LegacySchoolClassGrade::class, 'turma_id');
    }

    /**
     * Retorna os dias da semana em um array
     *
     * @param string $value
     * @return array|null
     */
    public function getDiasSemanaAttribute($value)
    {
        if (is_string($value)) {
            $value = explode(',', str_replace([
                '{',
                '}',
            ], '', $value));
        }

        return $value;
    }

    /**
     * Seta os dias da semana transformando um array em uma string
     *
     * @param array $values
     * @return void
     */
    public function setDiasSemanaAttribute($values)
    {
        if (is_array($values)) {
            $values = '{' . implode(',', $values) . '}';
        }
        $this->attributes['dias_semana'] = $values;
    }

    public function getActiveEnrollments() // @phpstan-ignore-line
    {
        return $this->enrollments()
            ->with([
                'registration' => function ($query) {
                    /** @var Builder $query */
                    $query->where('ano', $this->year); // @phpstan-ignore-line
                    $query->whereIn('aprovado', [
                        1,
                        2,
                        3,
                    ]);
                    $query->with('student.person');
                },
            ])
            ->where('ativo', 1)
            ->orderBy('sequencial_fechamento')
            ->get();
    }

    /**
     * @return BelongsTo<LegacySchoolGrade, $this>
     */
    public function schoolGrade(): BelongsTo
    {
        // @phpstan-ignore-next-line
        return $this->belongsTo(LegacySchoolGrade::class, 'ref_ref_cod_escola', 'ref_cod_escola')
            ->where('ref_cod_serie', $this->grade_id);
    }

    /**
     * Indica se bloqueia enturmações quando não houver vagas.
     *
     * @return bool
     */
    public function denyEnrollmentsWhenNoVacancy()
    {
        $schoolGrade = $this->schoolGrade;
        if (empty($schoolGrade)) {
            return true;
        }
        if (empty($schoolGrade->bloquear_enturmacao_sem_vagas)) {
            return true;
        }

        return (bool) $schoolGrade->bloquear_enturmacao_sem_vagas;
    }

    /**
     * Retorna o tempo de aula da turma em horas
     *
     * @return int
     */
    public function getClassTime()
    {
        if (!$this->hora_inicial || !$this->hora_final) {
            return 0;
        }
        $startTime = Carbon::createFromTimeString($this->hora_inicial);
        $endTime = Carbon::createFromTimeString($this->hora_final);

        return $startTime->diff($endTime)->h;
    }

    /**
     * @return BelongsToMany<LegacyDiscipline, $this>
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(
            LegacyDiscipline::class,
            'modules.componente_curricular_turma',
            'turma_id',
            'componente_curricular_id'
        )->withPivot([
            'ano_escolar_id',
            'escola_id',
            'carga_horaria',
            'docente_vinculado',
            'etapas_especificas',
            'etapas_utilizadas',
        ]);
    }

    /**
     * @return HasMany<Discipline, $this>
     */
    public function viewDisciplines(): HasMany
    {
        return $this->hasMany(Discipline::class, 'cod_turma', 'cod_turma');
    }

    /**
     * @return BelongsToMany<LegacyDiscipline, $this>
     */
    public function gradeDisciplines(): BelongsToMany
    {
        return $this->belongsToMany(
            LegacyDiscipline::class,
            'modules.componente_curricular_ano_escolar',
            'ano_escolar_id',
            'componente_curricular_id',
            'ref_ref_cod_serie',
            'id'
        )->withPivot([
            'carga_horaria',
            'tipo_nota',
        ]);
    }

    /**
     * @return Collection<int, Model|LegacyDiscipline>
     */
    public function getDisciplines(): Collection
    {
        if ((bool) $this->multiseriada) {
            // @phpstan-ignore-next-line
            $multigrades = $this->multigrades->pluck('serie_id')->toArray();

            return LegacySchoolGradeDiscipline::query()
                ->where('ref_ref_cod_escola', $this->school_id)
                ->whereIn('ref_ref_cod_serie', $multigrades)
                ->whereRaw('? = ANY(anos_letivos)', [$this->year])
                ->get()
                ->map(function ($schoolGrade) {
                    return $schoolGrade->discipline;
                });
        }
        $disciplinesOfSchoolClass = $this->disciplines()->get();
        if ($disciplinesOfSchoolClass->count() > 0) {
            return $disciplinesOfSchoolClass; // @phpstan-ignore-line
        }

        return LegacySchoolGradeDiscipline::query()
            ->where('ref_ref_cod_escola', $this->school_id)
            ->where('ref_ref_cod_serie', $this->grade_id)
            ->whereRaw('? = ANY(anos_letivos)', [$this->year])
            ->get()
            ->map(function ($schoolGrade) {
                return $schoolGrade->discipline;
            });
    }

    /**
     * Retorna a regra de avaliação que deve ser utilizada para a turma. Leva
     *  em consideração o parâmetro `utiliza_regra_diferenciada` da escola.
     *
     * @param int|null $gradeId
     */
    public function getEvaluationRule($gradeId = null): LegacyEvaluationRule
    {
        // a turma pode ser multisseriada e prover de várias séries
        // portando é necessária repassar em vez de pegar a série principal da turma
        $evaluationRuleGradeYear = LegacyEvaluationRuleGradeYear::query()
            ->where('serie_id', $gradeId ?? $this->ref_ref_cod_serie)
            ->where('ano_letivo', $this->ano)
            ->firstOrFail();
        if ($this->school->utiliza_regra_diferenciada && $evaluationRuleGradeYear->differentiatedEvaluationRule) {
            return $evaluationRuleGradeYear->differentiatedEvaluationRule;
        }

        return $evaluationRuleGradeYear->evaluationRule;
    }

    /**
     * Retorna o turno da turma.
     *
     * @return BelongsTo<LegacyPeriod, $this>
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(LegacyPeriod::class, 'turma_turno_id');
    }

    /**
     * @return HasOne<SchoolClassInep, $this>
     */
    public function inep(): HasOne
    {
        return $this->hasOne(SchoolClassInep::class, 'cod_turma');
    }
}
