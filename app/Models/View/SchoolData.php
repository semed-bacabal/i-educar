<?php

namespace App\Models\View;

use App\Services\Reports\Util;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $nome
 * @property string $telefone
 * @property string $telefone_ddd
 * @property string $celular
 * @property string $celular_ddd
 * @property string $logradouro
 * @property string $numero
 * @property string $bairro
 * @property string $cep
 * @property string $municipio
 * @property string $uf_municipio
 */
class SchoolData extends Model
{
    protected $primaryKey = 'cod_escola';

    protected $table = 'relatorio.view_dados_escola';

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nome
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->telefone ? '(' . $this->telefone_ddd . ') ' . $this->telefone : '(##) ####-####'
        );
    }

    protected function cellphone(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->celular ? '(' . $this->celular_ddd . ') ' . $this->celular : '(##) #####-####'
        );
    }

    protected function address(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', [
                $this->logradouro,
                $this->numero ? 'Nº.: ' . $this->numero : 'S/N',
                $this->bairro,
            ]) . ' - ' . $this->municipio . ' - ' . $this->uf_municipio . ' - CEP: ' . Util::formatPostcode($this->cep)
        );
    }

    protected function postpone(): Attribute
    {
        return Attribute::make(
            get: fn () => Util::formatPostcode($this->cep)
        );
    }
}
