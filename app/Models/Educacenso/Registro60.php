<?php

namespace App\Models\Educacenso;

use iEducar\Modules\Educacenso\Model\PaisResidencia;
use iEducar\Modules\Educacenso\Model\PoderPublicoTransporte;
use iEducar\Modules\Educacenso\Model\TipoAtendimentoTurma;
use iEducar\Modules\Educacenso\Model\TipoMediacaoDidaticoPedagogico;

class Registro60 implements ItemOfRegistro30, RegistroEducacenso
{
    public $registro;

    public $inepEscola;

    public $codigoPessoa;

    public $inepAluno;

    public $inepTurma;

    public $matriculaAluno;

    public $etapaAluno;

    public $tipoItinerarioLinguagens;

    public $tipoItinerarioMatematica;

    public $tipoItinerarioCienciasNatureza;

    public $tipoItinerarioCienciasHumanas;

    public $tipoItinerarioFormacaoTecnica;

    public $tipoItinerarioIntegrado;

    public $composicaoItinerarioLinguagens;

    public $composicaoItinerarioMatematica;

    public $composicaoItinerarioCienciasNatureza;

    public $composicaoItinerarioCienciasHumanas;

    public $composicaoItinerarioFormacaoTecnica;

    public $codCursoProfissional;

    public $cursoItinerario;

    public $itinerarioConcomitante;

    public $tipoAtendimentoDesenvolvimentoFuncoesGognitivas;

    public $tipoAtendimentoDesenvolvimentoVidaAutonoma;

    public $tipoAtendimentoEnriquecimentoCurricular;

    public $tipoAtendimentoEnsinoInformaticaAcessivel;

    public $tipoAtendimentoEnsinoLibras;

    public $tipoAtendimentoEnsinoLinguaPortuguesa;

    public $tipoAtendimentoEnsinoSoroban;

    public $tipoAtendimentoEnsinoBraile;

    public $tipoAtendimentoEnsinoOrientacaoMobilidade;

    public $tipoAtendimentoEnsinoCaa;

    public $tipoAtendimentoEnsinoRecursosOpticosNaoOpticos;

    public $recebeEscolarizacaoOutroEspacao;

    public $transportePublico;

    public $poderPublicoResponsavelTransporte;

    public $veiculoTransporteBicicleta;

    public $veiculoTransporteMicroonibus;

    public $veiculoTransporteOnibus;

    public $veiculoTransporteTracaoAnimal;

    public $veiculoTransporteVanKonbi;

    public $veiculoTransporteOutro;

    public $veiculoTransporteAquaviarioCapacidade5;

    public $veiculoTransporteAquaviarioCapacidade5a15;

    public $veiculoTransporteAquaviarioCapacidade15a35;

    public $veiculoTransporteAquaviarioCapacidadeAcima35;

    public $modalidadeCurso;

    public $turnoId;

    /**
     * @var ?int Campo usado somente na análise
     */
    public $turmaClasseEspecial;

    /**
     * @var string Campo usado somente na análise
     */
    public $nomeEscola;

    /**
     * @var string Campo usado somente na análise
     */
    public $nomeAluno;

    /**
     * @var string Campo usado somente na análise
     */
    public $codigoAluno;

    /**
     * @var string Campo usado somente na análise
     */
    public $tipoAtendimentoTurma;

    /**
     * @var string Campo usado somente na análise
     */
    public $codigoTurma;

    /**
     * @var string Campo usado somente na análise
     */
    public $etapaTurma;

    /**
     * @var array Campo usado somente na análise
     */
    public $organizacaoCurricularTurma;

    /**
     * @var int Campo usado somente na análise
     */
    public $enturmacaoId;

    /**
     * @var string Campo usado somente na análise
     */
    public $codigoMatricula;

    /**
     * @var string Campo usado somente na análise
     */
    public $nomeTurma;

    /**
     * @var string Campo usado somente na análise
     */
    public $tipoAtendimentoMatricula;

    /**
     * @var string Campo usado somente na análise
     */
    public $tipoMediacaoTurma;

    /**
     * @var string Campo usado somente na análise
     */
    public $veiculoTransporteEscolar;

    /**
     * @var string Campo usado somente na análise
     */
    public $localFuncionamentoDiferenciadoTurma;

    /**
     * @var string Campo usado somente na análise
     */
    public $paisResidenciaAluno;

    /**
     * @return bool
     */
    public function transportePublicoRequired()
    {
        $tiposMediacaoPresencial = [
            TipoMediacaoDidaticoPedagogico::PRESENCIAL,
            TipoMediacaoDidaticoPedagogico::SEMIPRESENCIAL,
        ];

        return in_array(TipoAtendimentoTurma::CURRICULAR_ETAPA_ENSINO, $this->tipoAtendimentoTurma)
            && in_array($this->tipoMediacaoTurma, $tiposMediacaoPresencial)
            && $this->paisResidenciaAluno == PaisResidencia::BRASIL;
    }

    /**
     * @return bool
     */
    public function veiculoTransporteEscolarRequired()
    {
        $transportePublico = [
            PoderPublicoTransporte::MUNICIPAL,
            PoderPublicoTransporte::ESTADUAL,
        ];

        return in_array($this->transportePublico, $transportePublico);
    }

    public function isAtividadeComplementarOrAee()
    {
        return in_array(TipoAtendimentoTurma::ATIVIDADE_COMPLEMENTAR, $this->tipoAtendimentoTurma) ||
            in_array(TipoAtendimentoTurma::AEE, $this->tipoAtendimentoTurma);
    }

    /**
     * @return bool
     */
    public function recebeEscolarizacaoOutroEspacoIsRequired()
    {
        return in_array(TipoAtendimentoTurma::CURRICULAR_ETAPA_ENSINO, $this->tipoAtendimentoTurma) &&
            $this->tipoMediacaoTurma == TipoMediacaoDidaticoPedagogico::PRESENCIAL &&
            $this->localFuncionamentoDiferenciadoTurma == \App_Model_LocalFuncionamentoDiferenciado::NAO_ESTA &&
            $this->localFuncionamentoDiferenciadoTurma == \App_Model_LocalFuncionamentoDiferenciado::SALA_ANEXA;
    }

    /**
     * @return bool
     */
    public function tipoItinerarioNaoPreenchido()
    {
        return
            !$this->tipoItinerarioLinguagens &&
            !$this->tipoItinerarioMatematica &&
            !$this->tipoItinerarioCienciasNatureza &&
            !$this->tipoItinerarioCienciasHumanas &&
            !$this->tipoItinerarioFormacaoTecnica &&
            !$this->tipoItinerarioIntegrado;
    }

    /**
     * @return bool
     */
    public function composicaoItinerarioNaoPreenchido()
    {
        return
            !$this->composicaoItinerarioLinguagens &&
            !$this->composicaoItinerarioMatematica &&
            !$this->composicaoItinerarioCienciasNatureza &&
            !$this->composicaoItinerarioCienciasHumanas &&
            !$this->composicaoItinerarioFormacaoTecnica;
    }

    public function etapaTurmaDescritiva()
    {
        $etapasEducacenso = loadJson('educacenso_json/etapas_ensino.json');

        return $etapasEducacenso[$this->etapaTurma];
    }

    public function getCodigoPessoa()
    {
        return $this->codigoPessoa;
    }

    public function getCodigoAluno()
    {
        return $this->codigoAluno;
    }

    public function getCodigoServidor()
    {
        return null;
    }
}
