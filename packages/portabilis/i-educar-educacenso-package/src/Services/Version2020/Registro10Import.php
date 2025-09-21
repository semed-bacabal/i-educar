<?php

namespace iEducar\Packages\Educacenso\Services\Version2020;

use App\Models\Educacenso\Registro10;
use App\Models\Educacenso\RegistroEducacenso;
use App\Models\LegacySchool;
use iEducar\Packages\Educacenso\Services\Version2019\Registro10Import as Registro10Import2019;
use iEducar\Packages\Educacenso\Services\Version2020\Models\Registro10Model;

class Registro10Import extends Registro10Import2019
{
    /**
     * Faz a importação dos dados a partir da linha do arquivo
     *
     * @param int                $year
     * @return void
     */
    public function import(RegistroEducacenso $model, $year, $user): void
    {
        parent::import($model, $year, $user);

        $schoolInep = parent::getSchool();

        if (empty($schoolInep)) {
            return;
        }

        /** @var LegacySchool $school */
        $school = $schoolInep->school;
        $model = $this->model;

        $school->qtd_vice_diretor = $model->qtdViceDiretor ?: null;
        $school->qtd_orientador_comunitario = $model->qtdOrientadorComunitario ?: null;

        $school->save();
    }

    /**
     * @return Registro10|RegistroEducacenso
     */
    public static function getModel($arrayColumns)
    {
        $registro = new Registro10Model();
        $registro->hydrateModel($arrayColumns);

        return $registro;
    }
}
