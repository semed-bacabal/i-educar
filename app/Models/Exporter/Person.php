<?php

namespace App\Models\Exporter;

use App\Models\Exporter\Builders\PersonEloquentBuilder;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Person extends Model
{
    /** @use HasBuilder<PersonEloquentBuilder> */
    use HasBuilder;

    protected static string $builder = PersonEloquentBuilder::class;

    /**
     * @var string
     */
    protected $table = 'exporter_person';

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
            'Aluno' => [
                'id' => 'ID',
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
                'phones.phones' => 'Telefones',
                'disabilities.disabilities' => 'Deficiências',
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
            'Mãe' => [
                'mother.id' => 'ID da mãe',
                'mother.name' => 'Nome da mãe',
                'mother.social_name' => 'Nome social e/ou afetivo da mãe',
                'mother.cpf' => 'CPF da mãe',
                'mother.rg' => 'RG da mãe',
                'mother.rg_issue_date' => 'RG (Data Emissão) da mãe',
                'mother.rg_state_abbreviation' => 'RG (Estado) da mãe',
                'mother.date_of_birth' => 'Data de nascimento da mãe',
                'mother.email' => 'E-mail da mãe',
                'mother.sus' => 'Número SUS da mãe',
                'mother.nis' => 'NIS (PIS/PASEP) da mãe',
                'mother.occupation' => 'Ocupação da mãe',
                'mother.organization' => 'Empresa da mãe',
                'mother.monthly_income' => 'Renda Mensal da mãe',
                'mother.gender' => 'Gênero da mãe',
            ],
            'Pai' => [
                'father.id' => 'ID do pai',
                'father.name' => 'Nome do pai',
                'father.social_name' => 'Nome social e/ou afetivo do pai',
                'father.cpf' => 'CPF do pai',
                'father.rg' => 'RG do pai',
                'father.rg_issue_date' => 'RG (Data Emissão) do pai',
                'father.rg_state_abbreviation' => 'RG (Estado) do pai',
                'father.date_of_birth' => 'Data de nascimento do pai',
                'father.email' => 'E-mail do pai',
                'father.sus' => 'Número SUS do pai',
                'father.nis' => 'NIS (PIS/PASEP) do pai',
                'father.occupation' => 'Ocupação do pai',
                'father.organization' => 'Empresa do pai',
                'father.monthly_income' => 'Renda Mensal do pai',
                'father.gender' => 'Gênero do pai',
            ],
            'Responsável' => [
                'guardian.id' => 'ID do responsável',
                'guardian.name' => 'Nome do responsável',
                'guardian.social_name' => 'Nome social e/ou afetivo do responsável',
                'guardian.cpf' => 'CPF do responsável',
                'guardian.rg' => 'RG do responsável',
                'guardian.rg_issue_date' => 'RG (Data Emissão) do responsável',
                'guardian.rg_state_abbreviation' => 'RG (Estado) do responsável',
                'guardian.date_of_birth' => 'Data de nascimento do responsável',
                'guardian.email' => 'E-mail do responsável',
                'guardian.sus' => 'Número SUS do responsável',
                'guardian.nis' => 'NIS (PIS/PASEP) do responsável',
                'guardian.occupation' => 'Ocupação do responsável',
                'guardian.organization' => 'Empresa do responsável',
                'guardian.monthly_income' => 'Renda Mensal do responsável',
                'guardian.gender' => 'Gênero do responsável',
            ],
        ];
    }

    public function getLabel(): string
    {
        return 'Pessoas';
    }

    public function getDescription(): string
    {
        return 'Exportação de pessoas';
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
