<?php

trait Avaliacao_Service_Boletim_Acessores
{
    use Avaliacao_Service_Boletim_Avaliacao;
    use Avaliacao_Service_Boletim_FaltaAluno;
    use Avaliacao_Service_Boletim_NotaAluno;
    use Avaliacao_Service_Boletim_ParecerDescritivoAluno;
    use Avaliacao_Service_Boletim_RegraAvaliacao;
    use Avaliacao_Service_Boletim_Validators;

    /**
     * @var array
     */
    protected $_options = [
        'matricula' => null,
        'etapas' => null,
        'usuario' => null,
        'turmaId' => null,
        'ignorarDispensasParciais' => false,
        'etapa' => null,
    ];

    /**
     * @var ComponenteCurricular_Model_ComponenteDataMapper
     */
    protected $_componenteDataMapper;

    /**
     * @var ComponenteCurricular_Model_TurmaDataMapper
     */
    protected $_componenteTurmaDataMapper;

    /**
     * @var RegraAvaliacao_Model_RegraDataMapper
     */
    protected $_regraDataMapper;

    /**
     * @var Avaliacao_Model_NotaAlunoDataMapper
     */
    protected $_notaAlunoDataMapper;

    /**
     * @var Avaliacao_Model_NotaComponenteDataMapper
     */
    protected $_notaComponenteDataMapper;

    /**
     * @var Avaliacao_Model_NotaComponenteMediaDataMapper
     */
    protected $_notaComponenteMediaDataMapper;

    /**
     * @var Avaliacao_Model_FaltaAlunoDataMapper
     */
    protected $_faltaAlunoDataMapper;

    /**
     * @var Avaliacao_Model_FaltaAbstractDataMapper
     */
    protected $_faltaAbstractDataMapper;

    /**
     * @var Avaliacao_Model_ParecerDescritivoAlunoDataMapper
     */
    protected $_parecerDescritivoAlunoDataMapper;

    /**
     * @var Avaliacao_Model_ParecerDescritivoAbstractDataMapper
     */
    protected $_parecerDescritivoAbstractDataMapper;

    /**
     * @var Avaliacao_Model_NotaGeralAbstractDataMapper
     */
    protected $_notaGeralAbstractDataMapper;

    /**
     * @var Avaliacao_Model_NotaGeralDataMapper
     */
    protected $_notaGeralDataMapper;

    /**
     * @var Avaliacao_Model_MediaGeralDataMapper
     */
    protected $_mediaGeralDataMapper;

    /**
     * @var int
     */
    protected $_componenteCurricularId;

    /**
     * @var int
     */
    protected $_currentComponenteCurricular;

    /**
     * @var bool
     */
    protected $_updateScore = false;

    /**
     * Componentes que o aluno cursa, indexado pelo id de
     * ComponenteCurricular_Model_Componente.
     *
     * @var array
     */
    protected $_componentes;

    /**
     * @see CoreExt_Configurable::getOptions()
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @see CoreExt_Configurable::setOptions()
     *
     * @return $this
     *
     * @throws CoreExt_Service_Exception
     */
    public function setOptions(array $options = [])
    {
        if (!isset($options['matricula'])) {
            throw new CoreExt_Service_Exception('É necessário informar o número de matrícula do aluno.');
        }

        if (isset($options['ComponenteDataMapper'])) {
            $this->setComponenteDataMapper($options['ComponenteDataMapper']);
            unset($options['ComponenteDataMapper']);
        }

        if (isset($options['ComponenteTurmaDataMapper'])) {
            $this->setComponenteTurmaDataMapper($options['ComponenteTurmaDataMapper']);
            unset($options['ComponenteTurmaDataMapper']);
        }

        if (isset($options['RegraDataMapper'])) {
            $this->setRegraDataMapper($options['RegraDataMapper']);
            unset($options['RegraDataMapper']);
        }

        if (isset($options['NotaAlunoDataMapper'])) {
            $this->setNotaAlunoDataMapper($options['NotaAlunoDataMapper']);
            unset($options['NotaAlunoDataMapper']);
        }

        if (isset($options['NotaComponenteDataMapper'])) {
            $this->setNotaComponenteDataMapper($options['NotaComponenteDataMapper']);
            unset($options['NotaComponenteDataMapper']);
        }

        if (isset($options['NotaComponenteMediaDataMapper'])) {
            $this->setNotaComponenteMediaDataMapper($options['NotaComponenteMediaDataMapper']);
            unset($options['NotaComponenteMediaDataMapper']);
        }

        if (isset($options['FaltaAlunoDataMapper'])) {
            $this->setFaltaAlunoDataMapper($options['FaltaAlunoDataMapper']);
            unset($options['FaltaAlunoDataMapper']);
        }

        if (isset($options['FaltaAbstractDataMapper'])) {
            $this->setFaltaAbstractDataMapper($options['FaltaAbstractDataMapper']);
            unset($options['FaltaAbstractDataMapper']);
        }

        if (isset($options['ParecerDescritivoAlunoDataMapper'])) {
            $this->setParecerDescritivoAlunoDataMapper($options['ParecerDescritivoAlunoDataMapper']);
            unset($options['ParecerDescritivoAlunoDataMapper']);
        }

        if (isset($options['ParecerDescritivoAbstractDataMapper'])) {
            $this->setParecerDescritivoAbstractDataMapper($options['ParecerDescritivoAbstractDataMapper']);
            unset($options['ParecerDescritivoAbstractDataMapper']);
        }

        if (isset($options['NotaGeralAbstractDataMapper'])) {
            $this->setNotaGeralAbstractDataMapper($options['NotaGeralAbstractDataMapper']);
            unset($options['NotaGeralAbstractDataMapper']);
        }

        if (isset($options['NotaGeralDataMapper'])) {
            $this->setNotaGeralDataMapper($options['NotaGeralDataMapper']);
            unset($options['NotaGeralDataMapper']);
        }

        if (isset($options['MediaGeralDataMapper'])) {
            $this->setMediaGeralDataMapper($options['MediaGeralDataMapper']);
            unset($options['MediaGeralDataMapper']);
        }

        if (isset($options['componenteCurricularId'])) {
            $this->setComponenteCurricularId($options['componenteCurricularId']);
            unset($options['componenteCurricularId']);
        }

        if (isset($options['updateScore'])) {
            $this->setUpdateScore($options['updateScore']);
            unset($options['updateScore']);
        }

        $defaultOptions = array_keys($this->getOptions());
        $passedOptions = array_keys($options);

        if (count(array_diff($passedOptions, $defaultOptions)) > 0) {
            throw new InvalidArgumentException(
                sprintf('A classe %s não suporta as opções: %s.', get_class($this), implode(', ', $passedOptions))
            );
        }

        $this->_options = array_merge($this->getOptions(), $options);

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->_options[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;

        return $this;
    }

    /**
     * @return ComponenteCurricular_Model_ComponenteDataMapper
     */
    public function getComponenteDataMapper()
    {
        if (is_null($this->_componenteDataMapper)) {
            $this->setComponenteDataMapper(new ComponenteCurricular_Model_ComponenteDataMapper);
        }

        return $this->_componenteDataMapper;
    }

    /**
     * @return $this
     */
    public function setComponenteDataMapper(ComponenteCurricular_Model_ComponenteDataMapper $mapper)
    {
        $this->_componenteDataMapper = $mapper;

        return $this;
    }

    /**
     * @return ComponenteCurricular_Model_TurmaDataMapper
     */
    public function getComponenteTurmaDataMapper()
    {
        if (is_null($this->_componenteTurmaDataMapper)) {
            $this->setComponenteTurmaDataMapper(new ComponenteCurricular_Model_TurmaDataMapper);
        }

        return $this->_componenteTurmaDataMapper;
    }

    /**
     * @return $this
     */
    public function setComponenteTurmaDataMapper(ComponenteCurricular_Model_TurmaDataMapper $mapper)
    {
        $this->_componenteTurmaDataMapper = $mapper;

        return $this;
    }

    /**
     * @return RegraAvaliacao_Model_RegraDataMapper
     */
    public function getRegraDataMapper()
    {
        if (is_null($this->_regraDataMapper)) {
            $this->setRegraDataMapper(new RegraAvaliacao_Model_RegraDataMapper);
        }

        return $this->_regraDataMapper;
    }

    /**
     * @return $this
     */
    public function setRegraDataMapper(RegraAvaliacao_Model_RegraDataMapper $mapper)
    {
        $this->_regraDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_NotaAlunoDataMapper
     */
    public function getNotaAlunoDataMapper()
    {
        if (is_null($this->_notaAlunoDataMapper)) {
            $this->setNotaAlunoDataMapper(new Avaliacao_Model_NotaAlunoDataMapper);
        }

        return $this->_notaAlunoDataMapper;
    }

    /**
     * @return $this
     */
    public function setNotaAlunoDataMapper(Avaliacao_Model_NotaAlunoDataMapper $mapper)
    {
        $this->_notaAlunoDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_NotaComponenteDataMapper
     */
    public function getNotaComponenteDataMapper()
    {
        if (is_null($this->_notaComponenteDataMapper)) {
            $this->setNotaComponenteDataMapper(new Avaliacao_Model_NotaComponenteDataMapper);
        }

        return $this->_notaComponenteDataMapper;
    }

    /**
     * @return $this
     */
    public function setNotaComponenteDataMapper(Avaliacao_Model_NotaComponenteDataMapper $mapper)
    {
        $this->_notaComponenteDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_NotaComponenteMediaDataMapper
     */
    public function getNotaComponenteMediaDataMapper()
    {
        if (is_null($this->_notaComponenteMediaDataMapper)) {
            $this->setNotaComponenteMediaDataMapper(new Avaliacao_Model_NotaComponenteMediaDataMapper);
        }

        return $this->_notaComponenteMediaDataMapper;
    }

    /**
     * @return $this
     */
    public function setNotaComponenteMediaDataMapper(Avaliacao_Model_NotaComponenteMediaDataMapper $mapper)
    {
        $this->_notaComponenteMediaDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_FaltaAlunoDataMapper
     */
    public function getFaltaAlunoDataMapper()
    {
        if (is_null($this->_faltaAlunoDataMapper)) {
            $this->setFaltaAlunoDataMapper(new Avaliacao_Model_FaltaAlunoDataMapper);
        }

        return $this->_faltaAlunoDataMapper;
    }

    /**
     * @return $this
     */
    public function setFaltaAlunoDataMapper(Avaliacao_Model_FaltaAlunoDataMapper $mapper)
    {
        $this->_faltaAlunoDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_FaltaAbstractDataMapper
     */
    public function getFaltaAbstractDataMapper()
    {
        if (is_null($this->_faltaAbstractDataMapper)) {
            switch ($this->getRegraAvaliacaoTipoPresenca()) {
                case RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE:
                    $this->setFaltaAbstractDataMapper(new Avaliacao_Model_FaltaComponenteDataMapper);
                    break;

                case RegraAvaliacao_Model_TipoPresenca::GERAL:
                    $this->setFaltaAbstractDataMapper(new Avaliacao_Model_FaltaGeralDataMapper);
                    break;
            }
        }

        return $this->_faltaAbstractDataMapper;
    }

    /**
     * @return $this
     */
    public function setFaltaAbstractDataMapper(Avaliacao_Model_FaltaAbstractDataMapper $mapper)
    {
        $this->_faltaAbstractDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_ParecerDescritivoAlunoDataMapper
     */
    public function getParecerDescritivoAlunoDataMapper()
    {
        if (is_null($this->_parecerDescritivoAlunoDataMapper)) {
            $this->setParecerDescritivoAlunoDataMapper(new Avaliacao_Model_ParecerDescritivoAlunoDataMapper);
        }

        return $this->_parecerDescritivoAlunoDataMapper;
    }

    /**
     * @return $this
     */
    public function setParecerDescritivoAlunoDataMapper(Avaliacao_Model_ParecerDescritivoAlunoDataMapper $mapper)
    {
        $this->_parecerDescritivoAlunoDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_ParecerDescritivoAbstractDataMapper
     */
    public function getParecerDescritivoAbstractDataMapper()
    {
        if (is_null($this->_parecerDescritivoAbstractDataMapper)) {
            $class = match ($this->getRegraAvaliacaoTipoParecerDescritivo()) {
                RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_GERAL, RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_GERAL => 'Avaliacao_Model_ParecerDescritivoGeralDataMapper',
                RegraAvaliacao_Model_TipoParecerDescritivo::ANUAL_COMPONENTE, RegraAvaliacao_Model_TipoParecerDescritivo::ETAPA_COMPONENTE => 'Avaliacao_Model_ParecerDescritivoComponenteDataMapper',
                default => null
            };

            // Se não usar parecer descritivo, retorna NULL
            if ($class === null) {
                return null;
            }

            $this->setParecerDescritivoAbstractDataMapper(new $class);
        }

        return $this->_parecerDescritivoAbstractDataMapper;
    }

    /**
     * @return $this
     */
    public function setParecerDescritivoAbstractDataMapper(Avaliacao_Model_ParecerDescritivoAbstractDataMapper $mapper)
    {
        $this->_parecerDescritivoAbstractDataMapper = $mapper;

        return $this;
    }

    public function getNotaGeralAbstractDataMapper()
    {
        if (is_null($this->_notaGeralAbstractDataMapper)) {
            $class = 'Avaliacao_Model_NotaGeralDataMapper';

            $this->setNotaGeralAbstractDataMapper(new $class);
        }

        return $this->_notaGeralAbstractDataMapper;
    }

    public function setNotaGeralAbstractDataMapper(Avaliacao_Model_NotaGeralDataMapper $mapper)
    {
        $this->_notaGeralAbstractDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_NotaGeralDataMapper
     */
    public function getNotaGeralDataMapper()
    {
        if (is_null($this->_notaGeralDataMapper)) {
            $this->setNotaGeralDataMapper(new Avaliacao_Model_NotaGeralDataMapper);
        }

        return $this->_notaGeralDataMapper;
    }

    /**
     * @return $this
     */
    public function setNotaGeralDataMapper(Avaliacao_Model_NotaGeralDataMapper $mapper)
    {
        $this->_notaGeralDataMapper = $mapper;

        return $this;
    }

    /**
     * @return Avaliacao_Model_MediaGeralDataMapper
     */
    public function getMediaGeralDataMapper()
    {
        if (is_null($this->_mediaGeralDataMapper)) {
            $this->setMediaGeralDataMapper(new Avaliacao_Model_MediaGeralDataMapper);
        }

        return $this->_mediaGeralDataMapper;
    }

    /**
     * @return $this
     */
    public function setMediaGeralDataMapper(Avaliacao_Model_MediaGeralDataMapper $mapper)
    {
        $this->_mediaGeralDataMapper = $mapper;

        return $this;
    }

    /**
     * @return int
     */
    public function getComponenteCurricularId()
    {
        return $this->_componenteCurricularId;
    }

    /**
     * @param int $componenteCurricularId
     * @return $this
     */
    public function setComponenteCurricularId($componenteCurricularId)
    {
        $this->_componenteCurricularId = $componenteCurricularId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentComponenteCurricular()
    {
        return $this->_currentComponenteCurricular;
    }

    /**
     * @param int $componenteId
     * @return $this
     */
    public function setCurrentComponenteCurricular($componenteId)
    {
        $this->_currentComponenteCurricular = $componenteId;

        return $this;
    }

    /**
     * @return array
     */
    public function getComponentes()
    {
        return $this->_componentes;
    }

    /**
     * @return $this
     */
    protected function _setComponentes(array $componentes)
    {
        $this->_componentes = $componentes;

        return $this;
    }

    public function isUpdateScore(): bool
    {
        return $this->_updateScore;
    }

    public function setUpdateScore(bool $updateScore): void
    {
        $this->_updateScore = $updateScore;
    }
}
