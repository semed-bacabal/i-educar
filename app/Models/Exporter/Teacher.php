<?php

namespace App\Models\Exporter;

use App\Models\Exporter\Builders\TeacherEloquentBuilder;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Teacher extends Model
{
    /** @use HasBuilder<TeacherEloquentBuilder> */
    use HasBuilder;

    protected static string $builder = TeacherEloquentBuilder::class;

    /**
     * @var string
     */
    protected $table = 'exporter_teacher';

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
            'Códigos' => [
                'id' => 'ID Pessoa',
                'school_id' => 'ID Escola',
                'school_class_id' => 'ID Turma',
                'grade_id' => 'ID Série',
                'course_id' => 'ID Curso',
            ],
            'Professor' => [
                'name' => 'Nome',
                'social_name' => 'Nome social e/ou afetivo',
                'cpf' => 'CPF',
                'rg' => 'RG',
                'rg_issue_date' => 'RG (Data Emissão)',
                'rg_state_abbreviation' => 'RG (Estado)',
                'date_of_birth' => 'Data de nascimento',
                'email' => 'E-mail',
                'sus' => 'Número SUS',
                'nis' => 'NIS (PIS/PASEP)',
                'occupation' => 'Ocupação',
                'organization' => 'Empresa',
                'monthly_income' => 'Renda Mensal',
                'gender' => 'Gênero',
                'race' => 'Raça',
            ],
            'Escola' => [
                'school' => 'Escola',
                'school_class' => 'Turma',
                'grade' => 'Série',
                'course' => 'Curso',
                'year' => 'Ano',
                'disciplines.disciplines' => 'Disciplinas',
                'enrollments' => 'Matrículas',
            ],
            'Informações' => [
                'phones.phones' => 'Telefones',
                'disabilities.disabilities' => 'Deficiências',
                'schooling_degree' => 'Escolaridade',
                'high_school_type' => 'Tipo de ensino médio cursado',
                'employee_postgraduates_complete' => 'Pós-Graduações concluídas',
                'continuing_education_course' => 'Outros cursos de formação continuada',
                'complementacao_pedagogica' => 'Formação/Complementação pedagógica',
                'employee_graduation_complete' => 'Curso(s) superior(es) concluído(s)',
                'allocations.funcao_exercida' => 'Função exercida',
                'allocations.tipo_vinculo' => 'Tipo de vínculo',
            ],
            'Endereço' => [
                'place.address' => 'Logradouro',
                'place.number' => 'Número',
                'place.complement' => 'Complemento',
                'place.neighborhood' => 'Bairro',
                'place.postal_code' => 'CEP',
                'place.latitude' => 'Latitude',
                'place.longitude' => 'Longitude',
                'place.city' => 'Cidade',
                'place.state_abbreviation' => 'Sigla do Estado',
                'place.state' => 'Estado',
                'place.country' => 'País',
            ],
        ];
    }

    public function getLabel(): string
    {
        return 'Professores';
    }

    public function getDescription(): string
    {
        return 'Os dados exportados serão contabilizados por quantidade de professores(as) alocados(as) no ano filtrado, agrupando as informações de cursos de formação dos docentes.';
    }

    /**
     * @param string $column
     * @return mixed
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
