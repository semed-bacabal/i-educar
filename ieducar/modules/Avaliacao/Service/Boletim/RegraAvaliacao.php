<?php

trait Avaliacao_Service_Boletim_RegraAvaliacao
{
    /**
     * Instância da regra de avaliação, com o qual o serviço irá utilizar para
     * decidir o fluxo da lógica.
     *
     * @var RegraAvaliacao_Model_Regra
     */
    protected $_regra;

    protected $_codigoDisciplinasAglutinadas;

    public function codigoDisciplinasAglutinadas()
    {
        if (!isset($this->_codigoDisciplinasAglutinadas)) {
            if (empty($this->getRegraAvaliacao()->disciplinasAglutinadas)) {
                $this->_codigoDisciplinasAglutinadas = [];
            } else {
                $this->_codigoDisciplinasAglutinadas = explode(',', $this->getRegraAvaliacao()->disciplinasAglutinadas);
            }
        }

        return $this->_codigoDisciplinasAglutinadas;
    }

    /**
     * @return RegraAvaliacao_Model_Regra
     */
    public function getRegraAvaliacao()
    {
        return $this->_regra;
    }

    /**
     * @deprecated
     * @see Avaliacao_Service_Boletim_RegraAvaliacao::getRegraAvaliacao()
     *
     * @return RegraAvaliacao_Model_Regra
     */
    public function getRegra()
    {
        return $this->getRegraAvaliacao();
    }

    /**
     * @return $this
     */
    protected function _setRegra(RegraAvaliacao_Model_Regra $regra)
    {
        $this->_regra = $regra;

        return $this;
    }

    /**
     * Retorna o tipo de presença da regra de avaliação.
     *
     * @return int
     */
    public function getRegraAvaliacaoTipoPresenca()
    {
        return $this->getRegraAvaliacao()->get('tipoPresenca');
    }

    /**
     * Retorna o tipo de parecer descritivo da regra de avaliação.
     *
     * @return int
     */
    public function getRegraAvaliacaoTipoParecerDescritivo()
    {
        return (int) $this->getRegraAvaliacao()->get('parecerDescritivo');
    }

    /**
     * @return TabelaArredondamento_Model_Tabela
     */
    public function getRegraAvaliacaoTabelaArredondamento()
    {
        return $this->getRegraAvaliacao()->tabelaArredondamento;
    }

    /**
     * Retorna o tipo de nota da regra de avaliação.
     *
     * @return int
     */
    public function getRegraAvaliacaoTipoNota()
    {
        return $this->getRegraAvaliacao()->get('tipoNota');
    }

    /**
     * Retorna o tipo de progressão da regra de avaliação.
     *
     * @return int
     */
    public function getRegraAvaliacaoTipoProgressao()
    {
        return (int) $this->getRegraAvaliacao()->get('tipoProgressao');
    }

    /**
     * Retorna aprovar pela frequencia após exame da regra de avaliação.
     *
     * @return bool
     */
    public function getRegraAvaliacaoAprovarPelaFrequenciaAposExame()
    {
        return $this->getRegraAvaliacao()->get('aprovarPelaFrequenciaAposExame') && $this->hasRegraAvaliacaoFormulaRecuperacao();
    }

    /**
     * Retorna verdadeiro se a regra de avaliação permite reprovar o aluno automaticamente
     * após pegar dependências.
     *
     * @return bool
     */
    public function hasReprovarAutomaticamenteAposDependencias()
    {
        return $this->getRegraAvaliacao()->get('reprovarAutomaticamenteAposDependencias') > 0;
    }

    /**
     * Retorna a quantidade de disciplinas que o aluno pode pegar dependência.
     *
     * @return int
     */
    public function getQtdeReprovarAutomaticamenteAposDependencias()
    {
        return (int) $this->getRegraAvaliacao()->get('reprovarAutomaticamenteAposDependencias');
    }

    /**
     * Retorna "1" se a regra de avaliação é do tipo nota geral por etapa.
     *
     * @return string
     */
    public function getRegraAvaliacaoNotaGeralPorEtapa()
    {
        return $this->getRegraAvaliacao()->get('notaGeralPorEtapa');
    }

    /**
     * Retorna a média que o aluno deve atingir para ser aprovado.
     *
     * @return float
     */
    public function getRegraAvaliacaoMedia()
    {
        return $this->getRegraAvaliacao()->media;
    }

    /**
     * Retorna a média que o aluno deve atingir no exame final para ser
     * aprovado.
     *
     * @return float
     */
    public function getRegraAvaliacaoMediaRecuperacao()
    {
        return $this->getRegraAvaliacao()->mediaRecuperacao;
    }

    /**
     * Retorna "1" se a regra de avaliação permite definir componente
     * curricular por etapa.
     *
     * @return string
     */
    public function getRegraAvaliacaoDefinirComponentePorEtapa()
    {
        return $this->getRegraAvaliacao()->get('definirComponentePorEtapa');
    }

    /**
     * Retorna a quantidade de disciplinas que o aluno pode pegar dependência.
     *
     * @return int
     */
    public function getRegraAvaliacaoQtdDisciplinasDependencia()
    {
        return (int) $this->getRegraAvaliacao()->get('qtdDisciplinasDependencia');
    }

    /**
     * Retorna o percentual de presença que o aluno deve possuir.
     *
     * @return float
     */
    public function getRegraAvaliacaoPorcentagemPresenca()
    {
        return $this->getRegraAvaliacao()->get('porcentagemPresenca');
    }

    /**
     * Retorna a fórmula para o cálculo da média da recuperação.
     *
     * @return FormulaMedia_Model_FormulaDataMapper
     */
    public function getRegraAvaliacaoFormulaRecuperacao()
    {
        return $this->getRegraAvaliacao()->formulaRecuperacao;
    }

    /**
     * Retorna a fórmula para o cálculo da média da recuperação.
     *
     * @return FormulaMedia_Model_FormulaDataMapper
     */
    public function getRegraAvaliacaoFormulaMedia()
    {
        return $this->getRegraAvaliacao()->formulaMedia;
    }

    /**
     * Retorna a tabela de arredondamento conceitual.
     *
     * @return TabelaArredondamento_Model_TabelaDataMapper
     */
    public function getRegraAvaliacaoTabelaArredondamentoConceitual()
    {
        return $this->getRegraAvaliacao()->tabelaArredondamentoConceitual;
    }

    /**
     * Retorna a nota máxima possível para o exame final.
     *
     * @return float
     */
    public function getRegraAvaliacaoNotaMaximaExameFinal()
    {
        return $this->getRegraAvaliacao()->notaMaximaExameFinal;
    }

    /**
     * Retorna a quantidade de casas decimais que devem ser armazenadas para
     * uma nota.
     *
     * @return int
     */
    public function getRegraAvaliacaoQtdCasasDecimais()
    {
        return $this->getRegraAvaliacao()->qtdCasasDecimais;
    }

    /**
     * Retorna o código da instituição.
     *
     * @deprecated
     *
     * @return int
     */
    public function getRegraAvaliacaoInstituicao()
    {
        return $this->getRegraAvaliacao()->instituicao;
    }

    /**
     * Indica se a regra de avaliação possui recuperação final.
     *
     * @return bool
     */
    public function hasRegraAvaliacaoFormulaRecuperacao()
    {
        return !is_null($this->getRegraAvaliacao()->get('formulaRecuperacao'));
    }

    /**
     * Indica se a regra de avaliação possui fórmula para calcular a média da
     * recuperação.
     *
     * @return bool
     */
    public function hasRegraAvaliacaoMediaRecuperacao()
    {
        return boolval($this->getRegraAvaliacao()->get('mediaRecuperacao'));
    }

    /**
     * Indica se a regra de avaliação tem reprovação automática.
     *
     * @return bool
     */
    public function hasRegraAvaliacaoReprovacaoAutomatica()
    {
        return boolval($this->getRegraAvaliacao()->reprovacaoAutomatica);
    }

    /**
     * Indica se a regra de avaliação pode aprovar o aluno baseado na média
     * geral de todas as disciplinas.
     *
     * @return bool
     */
    public function hasRegraAvaliacaoAprovaMediaDisciplina()
    {
        return boolval($this->getRegraAvaliacao()->get('aprovaMediaDisciplina'));
    }

    /**
     * Indica se a regra de avaliação deve desconsiderar a obrigatoriedade dos
     * lançamentos de frequência para aprovar/reprovar o aluno.
     *
     * @return bool
     */
    public function getRegraAvaliacaoDesconsiderarLancamentoFrequencia()
    {
        return boolval($this->getRegraAvaliacao()->get('desconsiderarLancamentoFrequencia'));
    }

    /**
     * Indica se a regra de avaliação usa a progressão do regime cíclico
     *
     * @return bool
     */
    public function isCyclicRegime()
    {
        return $this->getRegraAvaliacaoTipoProgressao() == RegraAvaliacao_Model_TipoProgressao::NAO_CONTINUADA_MANUAL_CICLO;
    }
}
