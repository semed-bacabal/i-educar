<?php

abstract class CoreExt_Enum extends CoreExt_Singleton implements ArrayAccess
{
    /**
     * Array que emula um enum.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Retorna o valor para um dado índice de CoreExt_Enum.
     *
     * @param string|int $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function getValue($key)
    {
        return $this->_data[$key];
    }

    /**
     * Retorna todos os valores de CoreExt_Enum.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function getValues()
    {
        return array_values($this->_data);
    }

    /**
     * Retorna o valor da índice para um determinado valor.
     *
     * @param mixed $value
     * @return int|string
     */
    #[\ReturnTypeWillChange]
    public function getKey($value)
    {
        return array_search($value, $this->_data);
    }

    /**
     * Retorna todos os índices de CoreExt_Enum.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function getKeys()
    {
        return array_keys($this->_data);
    }

    /**
     * Retorna o array de enums.
     *
     * @return array
     */
    public function getEnums()
    {
        return $this->_data;
    }

    /**
     * Implementa offsetExists da interface ArrayAccess.
     *
     * @link   http://br2.php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param string|int $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->_data[$offset]);
    }

    /**
     * Implementa offsetUnset da interface ArrayAccess.
     *
     * @link  http://br2.php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @throws CoreExt_Exception
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new CoreExt_Exception('Um "' . get_class($this) . '" é um objeto read-only.');
    }

    /**
     * Implementa offsetSet da interface ArrayAccess.
     *
     * Uma objeto CoreExt_Enum é apenas leitura.
     *
     * @link   http://br2.php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param string|int $offset
     * @param mixed      $value
     *
     * @throws CoreExt_Exception
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new CoreExt_Exception('Um "' . get_class($this) . '" é um objeto read-only.');
    }

    /**
     * Implementa offsetGet da interface ArrayAccess.
     *
     * @link   http://br2.php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param string|int $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }
}
