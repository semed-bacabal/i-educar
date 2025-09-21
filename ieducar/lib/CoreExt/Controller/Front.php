<?php

class CoreExt_Controller_Front extends CoreExt_Controller_Abstract
{
    /**
     * Opções para definição de qual tipo de controller utilizar durante a
     * execução de dispatch().
     *
     * @var int
     */
    const CONTROLLER_FRONT = 1;

    const CONTROLLER_PAGE = 2;

    /**
     * A instância singleton de CoreExt_Controller_Interface.
     *
     * @var CoreExt_Controller_Interface|null
     */
    protected static $_instance;

    /**
     * Opções de configuração geral da classe.
     *
     * @var array
     */
    protected $_options = [
        'basepath' => null,
        'controller_type' => self::CONTROLLER_PAGE,
        'controller_dir' => 'Views',
    ];

    /**
     * Contém os valores padrão da configuração.
     *
     * @var array
     */
    protected $_defaultOptions = [];

    /**
     * Uma instância de CoreExt_View_Abstract
     *
     * @var CoreExt_View_Abstract
     */
    protected $_view;

    /**
     * Construtor singleton.
     */
    protected function __construct()
    {
        $this->_defaultOptions = $this->getOptions();
    }

    /**
     * Retorna a instância singleton.
     *
     * @return CoreExt_Controller_Front
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Recupera os valores de configuração original da instância.
     *
     * @return CoreExt_Configurable Provê interface fluída
     */
    public function resetOptions()
    {
        $this->setOptions($this->_defaultOptions);

        return $this;
    }

    /**
     * Encaminha a execução para o objeto CoreExt_Dispatcher_Interface apropriado.
     *
     * @return CoreExt_Controller_Interface Provê interface fluída
     *
     * @see CoreExt_Controller_Interface#dispatch()
     */
    public function dispatch()
    {
        $this->_getControllerStrategy()->dispatch();

        return $this;
    }

    /**
     * Retorna o conteúdo gerado pelo controller.
     *
     * @return string
     */
    public function getViewContents()
    {
        return $this->getView()->getContents();
    }

    /**
     * Setter.
     *
     *
     * @return CoreExt_Controller_Interface Provê interface fluída
     */
    public function setView(CoreExt_View_Abstract $view)
    {
        $this->_view = $view;

        return $this;
    }

    /**
     * Getter para uma instância de CoreExt_View_Abstract.
     *
     * Instância via lazy initialization uma instância de CoreExt_View caso
     * nenhuma seja explicitamente atribuída a instância atual.
     *
     * @return CoreExt_View_Abstract
     */
    public function getView()
    {
        if (is_null($this->_view)) {
            $this->setView(new CoreExt_View);
        }

        return $this->_view;
    }

    /**
     * Getter para uma instância de CoreExt_Controller_Dispatcher_Interface.
     *
     * Instância via lazy initialization uma instância de
     * CoreExt_Controller_Dispatcher caso nenhuma seja explicitamente
     * atribuída a instância atual.
     *
     * @return CoreExt_Controller_Dispatcher_Interface
     */
    public function getDispatcher()
    {
        if (is_null($this->_dispatcher)) {
            $this->setDispatcher($this->_getControllerStrategy());
        }

        return $this->_dispatcher;
    }

    /**
     * Getter para a estratégia de controller, definida em runtime.
     *
     * @return CoreExt_Controller_Strategy
     */
    protected function _getControllerStrategy()
    {
        $strategy = match ((int) $this->getOption('controller_type')) {
            1 => 'CoreExt_Controller_Dispatcher_Strategy_FrontStrategy',
            2 => 'CoreExt_Controller_Dispatcher_Strategy_PageStrategy',
            default => throw new Exception('Parâmetro de controlador incorreto'),
        };

        return new $strategy($this);
    }
}
