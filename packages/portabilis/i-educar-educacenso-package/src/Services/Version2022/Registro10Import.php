<?php

namespace iEducar\Packages\Educacenso\Services\Version2022;

use App\Models\Educacenso\Registro10;
use App\Models\Educacenso\RegistroEducacenso;
use iEducar\Modules\Educacenso\Model\Equipamentos;
use iEducar\Modules\Educacenso\Model\InstrumentosPedagogicos;
use iEducar\Modules\Educacenso\Model\Laboratorios;
use iEducar\Modules\Educacenso\Model\SalasAtividades;
use iEducar\Packages\Educacenso\Services\Version2020\Registro10Import as Registro10Import2020;
use iEducar\Packages\Educacenso\Services\Version2022\Models\Registro10Model;

class Registro10Import extends Registro10Import2020
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

        $school->nao_ha_funcionarios_para_funcoes = (bool) $model->semFuncionariosParaFuncoes;

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

    protected function getArrayLaboratorios()
    {
        $laboratorios = parent::getArrayLaboratorios();
        $arrayLaboratorios = transformStringFromDBInArray($laboratorios) ?: [];

        if ($this->model->dependenciaLaboratorioEducacaoProfissional) {
            $arrayLaboratorios[] = Laboratorios::EDUCACAO_PROFISSIONAL;
        }

        return parent::getPostgresIntegerArray($arrayLaboratorios);
    }

    protected function getArraySalasAtividades()
    {
        $salasAtividades = parent::getArraySalasAtividades();
        $arraySalas = transformStringFromDBInArray($salasAtividades) ?: [];

        if ($this->model->dependenciaSalaEducacaoProfissional) {
            $arraySalas[] = SalasAtividades::EDUCACAO_PROFISSIONAL;
        }

        return parent::getPostgresIntegerArray($arraySalas);
    }

    protected function getArrayEquipamentos()
    {
        $equipamentos = parent::getArrayEquipamentos();
        $arrayEquipamentos = transformStringFromDBInArray($equipamentos) ?: [];

        if ($this->model->equipamentosNenhum) {
            $arrayEquipamentos[] = Equipamentos::NENHUM_EQUIPAMENTO_LISTADO;
        }

        return parent::getPostgresIntegerArray($arrayEquipamentos);
    }

    protected function getArrayInstrumentosPedagogicos()
    {
        $instrumentos = parent::getArrayInstrumentosPedagogicos();
        $arrayInstrumentos = transformStringFromDBInArray($instrumentos) ?: [];

        if ($this->model->instrumentosPedagogicosEducacaoProfissional) {
            $arrayInstrumentos[] = InstrumentosPedagogicos::MATERIAL_EDUCACAO_PROFISSIONAL;
        }

        if ($this->model->instrumentosPedagogicosNenhum) {
            $arrayInstrumentos[] = InstrumentosPedagogicos::NENHUM_DOS_INSTRUMENTOS_LISTADOS;
        }

        return parent::getPostgresIntegerArray($arrayInstrumentos);
    }
}
