<?php

namespace iEducar\Packages\Educacenso\Services\Version2019\Models;

use App\Models\Educacenso\Registro40;

class Registro40Model extends Registro40
{
    public function hydrateModel(array $arrayColumns): void
    {
        array_unshift($arrayColumns, null);
        unset($arrayColumns[0]);

        $this->registro = $arrayColumns[1];
        $this->inepEscola = $arrayColumns[2];
        $this->codigoPessoa = $arrayColumns[3];
        $this->inepGestor = $arrayColumns[4];
        $this->cargo = $arrayColumns[5] ?: null;
        $this->criterioAcesso = $arrayColumns[6] ?: null;
        $this->especificacaoCriterioAcesso = $arrayColumns[7];
        $this->tipoVinculo = $arrayColumns[8] ?: null;
    }
}
