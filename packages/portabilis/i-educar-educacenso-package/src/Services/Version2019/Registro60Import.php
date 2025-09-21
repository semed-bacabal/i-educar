<?php

namespace iEducar\Packages\Educacenso\Services\Version2019;

use App\Models\Educacenso\Registro60;
use App\Models\Educacenso\RegistroEducacenso;
use App\Models\LegacyEnrollment;
use App\Models\LegacyInstitution;
use App\Models\LegacyRegistration;
use App\Models\LegacySchoolClass;
use App\Models\LegacyStudent;
use App\Models\SchoolClassInep;
use App\Models\StudentInep;
use App\User;
use App_Model_MatriculaSituacao;
use DateTime;
use iEducar\Modules\Educacenso\Model\TipoAtendimentoAluno;
use iEducar\Modules\Educacenso\Model\VeiculoTransporteEscolar;
use iEducar\Packages\Educacenso\Services\RegistroImportInterface;
use iEducar\Packages\Educacenso\Services\Version2019\Models\Registro60Model;

class Registro60Import implements RegistroImportInterface
{
    /**
     * @var Registro60
     */
    private $model;

    /**
     * @var User
     */
    private $user;

    /**
     * @var int
     */
    private $year;

    /**
     * @var LegacyInstitution
     */
    private $institution;

    /**
     * @var DateTime
     */
    public $registrationDate;

    /**
     * Faz a importação dos dados a partir da linha do arquivo
     *
     * @param int                $year
     * @return void
     */
    public function import(RegistroEducacenso $model, $year, $user): void
    {
        $this->year = $year;
        $this->user = $user;
        $this->model = $model;
        $this->institution = app(LegacyInstitution::class);

        $schoolClass = $this->getSchoolClass();
        if (! $schoolClass) {
            return;
        }

        $student = $this->getStudent();
        if (! $student) {
            return;
        }

        $this->storeStudentData($student);

        $registration = $this->getOrCreateRegistration($schoolClass, $student);
        $this->getOrCreateEnrollment($schoolClass, $registration);
    }

    /**
     * @return LegacySchoolClass
     */
    protected function getSchoolClass(): ?LegacySchoolClass
    {
        if (empty($this->model->inepTurma)) {
            return null;
        }

        return SchoolClassInep::where('cod_turma_inep', $this->model->inepTurma)->first()->schoolClass ?? null;
    }

    /**
     * @return LegacyStudent|null
     */
    protected function getStudent()
    {
        return StudentInep::where('cod_aluno_inep', $this->model->inepAluno)->first()->student ?? null;
    }

    /**
     * @return Registro50|RegistroEducacenso
     */
    public static function getModel($arrayColumns)
    {
        $registro = new Registro60Model();
        $registro->hydrateModel($arrayColumns);

        return $registro;
    }

    /**
     * @return LegacyRegistration
     */
    private function getOrCreateRegistration(LegacySchoolClass $schoolClass, LegacyStudent $student)
    {
        return LegacyRegistration::firstOrCreate(
            [
                'ref_ref_cod_serie' => $schoolClass->grade->getKey(),
                'ref_cod_aluno' => $student->getKey(),
                'ano' => $this->year,
            ],
            [
                'ref_usuario_cad' => $this->user->getKey(),
                'ativo' => 1,
                'aprovado' => App_Model_MatriculaSituacao::APROVADO,
                'ref_cod_curso' => $schoolClass->course->getKey(),
                'ref_ref_cod_escola' => $schoolClass->school->getKey(),
                'data_matricula' => $this->registrationDate,
                'data_cadastro' => now(),
                'ultima_matricula' => 1,
            ]
        );
    }

    /**
     * @return LegacyEnrollment
     */
    private function getOrCreateEnrollment(LegacySchoolClass $schoolClass, LegacyRegistration $registration)
    {
        $maxSequencial = LegacyEnrollment::where('ref_cod_matricula', $registration->getKey())->max('sequencial') ?: 0;

        return LegacyEnrollment::firstOrCreate(
            [
                'ref_cod_matricula' => $registration->getKey(),
                'ref_cod_turma' => $schoolClass->getKey(),
            ],
            [
                'data_cadastro' => now(),
                'data_enturmacao' => $this->registrationDate,
                'ativo' => 1,
                'etapa_educacenso' => $this->model->etapaAluno,
                'tipo_atendimento' => $this->getArrayTipoAtendimento(),
                'sequencial' => $maxSequencial + 1,
                'ref_usuario_cad' => $this->user->getKey(),
            ]
        );
    }

    /**
     * @return string
     */
    private function getArrayTipoAtendimento()
    {
        $arrayTipoAtendimento = [];

        if ($this->model->tipoAtendimentoDesenvolvimentoFuncoesGognitivas) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::DESENVOLVIMENTO_FUNCOES_COGNITIVAS;
        }

        if ($this->model->tipoAtendimentoEnriquecimentoCurricular) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENRIQUECIMENTO_CURRICULAR;
        }

        if ($this->model->tipoAtendimentoDesenvolvimentoVidaAutonoma) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::DESENVOLVIMENTO_VIDA_AUTONOMA;
        }

        if ($this->model->tipoAtendimentoEnriquecimentoCurricular) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENRIQUECIMENTO_CURRICULAR;
        }

        if ($this->model->tipoAtendimentoEnsinoInformaticaAcessivel) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENSINO_INFORMATICA_ACESSIVEL;
        }

        if ($this->model->tipoAtendimentoEnsinoLibras) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENSINO_LIBRAS;
        }

        if ($this->model->tipoAtendimentoEnsinoLinguaPortuguesa) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENSINO_LINGUA_PORTUGUESA;
        }

        if ($this->model->tipoAtendimentoEnsinoSoroban) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENSINO_SOROBAN;
        }

        if ($this->model->tipoAtendimentoEnsinoBraile) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENSINO_BRAILE;
        }

        if ($this->model->tipoAtendimentoEnsinoOrientacaoMobilidade) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENSINO_ORIENTACAO_MOBILIDADE;
        }

        if ($this->model->tipoAtendimentoEnsinoCaa) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENSINO_CAA;
        }

        if ($this->model->tipoAtendimentoEnsinoRecursosOpticosNaoOpticos) {
            $arrayTipoAtendimento[] = TipoAtendimentoAluno::ENSINO_RECURSOS_OPTICOS_E_NAO_OPTICOS;
        }

        return $this->getPostgresIntegerArray($arrayTipoAtendimento);
    }

    /**
     * @return string
     */
    private function getPostgresIntegerArray($array)
    {
        return '{' . implode(',', $array) . '}';
    }

    private function storeStudentData(LegacyStudent $student): void
    {
        $student->recebe_escolarizacao_em_outro_espaco = $this->model->recebeEscolarizacaoOutroEspacao;
        $student->tipo_transporte = $this->model->poderPublicoResponsavelTransporte ?: 0;
        $student->veiculo_transporte_escolar = $this->getArrayVeiculoTransporte();

        $student->save();
    }

    private function getArrayVeiculoTransporte()
    {
        $arrayVeiculoTransporte = [];

        if ($this->model->veiculoTransporteVanKonbi) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::VAN_KOMBI;
        }

        if ($this->model->veiculoTransporteMicroonibus) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::MICROONIBUS;
        }

        if ($this->model->veiculoTransporteOnibus) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::ONIBUS;
        }

        if ($this->model->veiculoTransporteBicicleta) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::BICICLETA;
        }

        if ($this->model->veiculoTransporteTracaoAnimal) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::TRACAO_ANIMAL;
        }

        if ($this->model->veiculoTransporteOutro) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::OUTRO;
        }

        if ($this->model->veiculoTransporteAquaviarioCapacidade5) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::CAPACIDADE_5;
        }

        if ($this->model->veiculoTransporteAquaviarioCapacidade5a15) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::CAPACIDADE_5_15;
        }

        if ($this->model->veiculoTransporteAquaviarioCapacidade15a35) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::CAPACIDADE_15_35;
        }

        if ($this->model->veiculoTransporteAquaviarioCapacidadeAcima35) {
            $arrayVeiculoTransporte[] = VeiculoTransporteEscolar::CAPACIDADE_35;
        }

        return $this->getPostgresIntegerArray($arrayVeiculoTransporte);
    }
}
