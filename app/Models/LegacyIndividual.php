<?php

namespace App\Models;

use App\Models\Builders\LegacyIndividualBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property LegacyPerson $mother
 * @property LegacyPerson $father
 * @property LegacyPerson $person
 * @property LegacyPerson $responsible
 * @property Carbon $data_nasc
 * @property string $sexo
 * @property string $social_name
 * @property int $nacionalidade
 */
class LegacyIndividual extends Model
{
    /** @use HasBuilder<LegacyIndividualBuilder> */
    use HasBuilder;

    use HasFiles;

    public const CREATED_AT = 'data_cad';

    public const UPDATED_AT = null;

    protected static string $builder = LegacyIndividualBuilder::class;

    protected $table = 'cadastro.fisica';

    protected $primaryKey = 'idpes';

    protected $casts = [
        'data_nasc' => 'date',
        'data_admissao' => 'date',
    ];

    protected $fillable = [
        'idpes',
        'data_nascimento',
        'zona_localizacao_censo',
        'idpes',
        'data_nasc',
        'sexo',
        'idpes_mae',
        'idpes_pai',
        'idpes_responsavel',
        'idesco',
        'ideciv',
        'idpes_con',
        'data_uniao',
        'data_obito',
        'nacionalidade',
        'idpais_estrangeiro',
        'data_chegada_brasil',
        'idmun_nascimento',
        'ultima_empresa',
        'nome_mae',
        'nome_pai',
        'nome_conjuge',
        'nome_responsavel',
        'justificativa_provisorio',
        'idpes_rev',
        'data_rev',
        'origem_gravacao',
        'idpes_cad',
        'data_cad',
        'operacao',
        'ref_cod_sistema',
        'cpf',
        'ref_cod_religiao',
        'nis_pis_pasep',
        'sus',
        'ocupacao',
        'empresa',
        'pessoa_contato',
        'renda_mensal',
        'data_admissao',
        'ddd_telefone_empresa',
        'telefone_empresa',
        'falecido',
        'ativo',
        'ref_usuario_exc',
        'data_exclusao',
        'zona_localizacao_censo',
        'tipo_trabalho',
        'local_trabalho',
        'horario_inicial_trabalho',
        'horario_final_trabalho',
        'nome_social',
        'pais_residencia',
        'localizacao_diferenciada',
        'ideciv',
        'observacao',
        'povo_indigena_educacenso_id',
    ];

    /**
     * @return BelongsToMany<LegacyRace, $this>
     */
    public function race(): BelongsToMany
    {
        return $this->belongsToMany(
            LegacyRace::class,
            'cadastro.fisica_raca',
            'ref_idpes',
            'ref_cod_raca'
        );
    }

    /**
     * @return BelongsToMany<LegacyRace, $this>
     */
    public function races(): BelongsToMany
    {
        return $this->belongsToMany(
            LegacyRace::class,
            'cadastro.fisica_raca',
            'ref_idpes',
            'ref_cod_raca',
            'idpes',
            'cod_raca'
        );
    }

    /**
     * @return BelongsToMany<LegacyDeficiency, $this>
     */
    public function deficiency(): BelongsToMany
    {
        return $this->belongsToMany(
            LegacyDeficiency::class,
            'cadastro.fisica_deficiencia',
            'ref_idpes',
            'ref_cod_deficiencia'
        );
    }

    /**
     * @return BelongsTo<LegacyPerson, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(LegacyPerson::class, 'idpes', 'idpes');
    }

    /**
     * @return HasMany<LegacyPhone, $this>
     */
    public function phones(): HasMany
    {
        return $this->hasMany(LegacyPhone::class, 'idpes', 'idpes');
    }

    /**
     * @return HasOne<LegacyPhone, $this>
     */
    public function phone(): HasOne
    {
        // @phpstan-ignore-next-line
        return $this->hasOne(LegacyPhone::class, 'idpes', 'idpes')->where('fone', '<>', 0)->orderBy('tipo');
    }

    /**
     * @return HasOne<LegacyStudent, $this>
     */
    public function student(): HasOne
    {
        return $this->hasOne(LegacyStudent::class, 'ref_idpes', 'idpes');
    }

    /**
     * @return BelongsTo<LegacyMaritalStatus, $this>
     */
    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(LegacyMaritalStatus::class, 'ideciv');
    }

    /**
     * @return BelongsTo<Religion, $this>
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class, 'ref_cod_religiao');
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function foreignCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'idpais_estrangeiro');
    }

    /**
     * @return BelongsTo<LegacyPerson, $this>
     */
    public function mother(): BelongsTo
    {
        return $this->belongsTo(LegacyPerson::class, 'idpes_mae', 'idpes');
    }

    /**
     * @return BelongsTo<LegacyPerson, $this>
     */
    public function father(): BelongsTo
    {
        return $this->belongsTo(LegacyPerson::class, 'idpes_pai', 'idpes');
    }

    /**
     * @return BelongsTo<LegacyPerson, $this>
     */
    public function responsible()
    {
        return $this->belongsTo(LegacyPerson::class, 'idpes_responsavel', 'idpes');
    }

    /**
     * @return HasOne<LegacyDocument, $this>
     */
    public function document(): HasOne
    {
        return $this->hasOne(LegacyDocument::class, 'idpes');
    }

    /**
     * @return HasOne<LegacyIndividualPicture, $this>
     */
    public function picture(): HasOne
    {
        return $this->hasOne(LegacyIndividualPicture::class, 'idpes');
    }

    /**
     * @return BelongsTo<LegacyCity, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(LegacyCity::class, 'idmun_nascimento', 'idmun');
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function cityBirth(): BelongsTo
    {
        return $this->belongsTo(City::class, 'idmun_nascimento');
    }

    /**
     * {@inheritDoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->data_cad = now();
            $model->origem_gravacao = 'M';
            $model->operacao = 'I';
            $model->pais_residencia = $model->pais_residencia ?? 76;
        });
    }

    public static function findByCpf(string|int $cpf): ?Model
    {
        $cpf = preg_replace('/\D/', '', (string) $cpf);

        if ($cpf === null) {
            return null;
        }

        return static::query()->where('cpf', (int) $cpf)->first();
    }

    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? int2CPF($value) : null,
        );
    }

    protected function genderName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sexo ? $this->sexo === 'M' ? 'Masculino' : 'Feminino' : null
        );
    }

    protected function nationalityName(): Attribute
    {
        return Attribute::make(
            get: fn () => (new Nationality)->getDescriptiveValues()[$this->nacionalidade]
        );
    }

    protected function socialName(): Attribute
    {
        return Attribute::make(
            get: fn () => !empty($this->nome_social) ? $this->nome_social : null
        );
    }

    protected function realName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->social_name) {
                    return $this->social_name;
                }

                return $this->person->name;
            }
        );
    }

    protected function birthdate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->data_nasc,
        );
    }

    protected function parentsName(): Attribute
    {
        return Attribute::make(
            get: function () {
                $data = collect();
                $data->push($this->father->name);
                $data->push($this->mother->name);

                return $data->filter()->implode(' e ');
            },
        );
    }
}
