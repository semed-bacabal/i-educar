<?php

use Tests\TestCase;

abstract class UnitBaseTest extends TestCase
{
    /**
     * Métodos a serem excluídos da lista de métodos a serem mockados por
     * getCleanMock().
     *
     * @var array
     */
    protected $_excludedMethods = [];

    /**
     * Setter para o atributo $_excludedMethods.
     *
     *
     * @return UnitBaseTest Provê interface fluída
     */
    public function setExcludedMethods(array $methods)
    {
        $this->_excludedMethods = $methods;

        return $this;
    }

    /**
     * Getter para o atributo $_excludedMethods.
     *
     * @return array
     */
    public function getExcludedMethods()
    {
        return $this->_excludedMethods;
    }

    /**
     * Reseta o valor do atributo $_excludedMethods.
     *
     * @return TestCase Provê interface fluída
     */
    public function resetExcludedMethods()
    {
        $this->_excludedMethods = [];

        return $this;
    }

    /**
     * Remove os métodos indicados por setExcludedMethods() da lista de métodos
     * a serem mockados.
     *
     *
     * @return array
     */
    protected function _cleanMockMethodList(array $methods)
    {
        foreach ($methods as $key => $method) {
            if (array_search($method, $this->getExcludedMethods()) !== false) {
                unset($methods[$key]);
            }
        }
        $this->resetExcludedMethods();

        return $methods;
    }

    /**
     * Retorna um objeto mock do PHPUnit, alterando os valores padrões dos
     * parâmetros $call* para FALSE.
     *
     * Faz uma limpeza da lista de métodos a serem mockados ao chamar
     * _cleanMockMethodList().
     *
     * @param string $className
     * @param string $mockName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callOriginalAutoload
     * @return PHPUnit\Framework\MockObject\MockObject
     */
    public function getCleanMock(
        $className,
        array $mockMethods = [],
        array $args = [],
        $mockName = '',
        $callOriginalConstructor = false,
        $callOriginalClone = false,
        $callOriginalAutoload = false
    ) {
        if (count($mockMethods) == 0) {
            $reflectiveClass = new ReflectionClass($className);
            $methods = $reflectiveClass->getMethods();
            $mockMethods = [];

            foreach ($methods as $method) {
                if (!$method->isFinal() && !$method->isAbstract() && !$method->isPrivate()) {
                    $mockMethods[] = $method->name;
                }
            }
        }

        $mockMethods = $this->_cleanMockMethodList($mockMethods);

        if ($mockName == '') {
            $mockName = $className . '_Mock_' . substr(md5(uniqid()), 0, 6);
        }

        $mockBuilder = $this->getMockBuilder($className)
            ->setConstructorArgs($args)
            ->setMockClassName($mockName);

        if (!$callOriginalConstructor) {
            $mockBuilder->disableOriginalConstructor();
        }

        if (!$callOriginalClone) {
            $mockBuilder->disableOriginalClone();
        }

        if (!empty($mockMethods)) {
            $mockBuilder->onlyMethods($mockMethods);
        }

        return $mockBuilder->getMock();
    }

    /**
     * Retorna um mock da classe de conexão clsBanco.
     *
     * @return clsBanco
     */
    public function getDbMock()
    {
        // Cria um mock de clsBanco, preservando o código do método formatValues
        return $this->setExcludedMethods(['formatValues'])
            ->getCleanMock('clsBanco');
    }

    /**
     * Controla o buffer de saída.
     *
     * @param bool $enable
     * @return bool|string
     */
    public function outputBuffer($enable = true)
    {
        if ($enable == true) {
            ob_start();

            return true;
        } else {
            $contents = ob_get_contents();
            ob_end_clean();

            return $contents;
        }
    }
}
