<?php

use iEducar\Legacy\Model;

class clsPmieducarEscolaSerieDisciplina extends Model
{
    public $ref_ref_cod_serie;

    public $ref_ref_cod_escola;

    public $ref_cod_disciplina;

    public $ativo;

    public $carga_horaria;

    public $etapas_especificas;

    public $etapas_utilizadas;

    public $anos_letivos;

    public $hora_falta;

    public $aulas_por_semana;

    public function __construct(
        $ref_ref_cod_serie = null,
        $ref_ref_cod_escola = null,
        $ref_cod_disciplina = null,
        $ativo = null,
        $carga_horaria = false,
        $etapas_especificas = false,
        $etapas_utilizadas = false,
        $anos_letivos = [],
        $hora_falta = null,
        $aulas_por_semana = null
    ) {

        $this->_schema = 'pmieducar.';
        $this->_tabela = $this->_schema . 'escola_serie_disciplina';

        $this->_campos_lista = $this->_todos_campos = 'ref_ref_cod_serie, ref_ref_cod_escola, ref_cod_disciplina, carga_horaria, etapas_especificas, etapas_utilizadas, ARRAY_TO_JSON(anos_letivos) AS anos_letivos, hora_falta, aulas_por_semana ';

        if (is_numeric($ref_cod_disciplina)) {
            $componenteMapper = new ComponenteCurricular_Model_ComponenteDataMapper;

            try {
                $componenteMapper->find($ref_cod_disciplina);
                $this->ref_cod_disciplina = $ref_cod_disciplina;
            } catch (Exception) {
            }
        }

        if (is_numeric($ref_ref_cod_escola) && is_numeric($ref_ref_cod_serie)) {
            $this->ref_ref_cod_escola = $ref_ref_cod_escola;
            $this->ref_ref_cod_serie = $ref_ref_cod_serie;
        } else {
            $this->ref_ref_cod_serie = $ref_ref_cod_serie;
        }

        if (is_numeric($ativo)) {
            $this->ativo = $ativo;
        }

        if (is_numeric($carga_horaria)) {
            $this->carga_horaria = $carga_horaria;
        } elseif (is_null($carga_horaria)) {
            $this->carga_horaria = null;
        }

        if (is_numeric($hora_falta)) {
            $this->hora_falta = $hora_falta;
        } elseif (is_null($hora_falta)) {
            $this->hora_falta = null;
        }

        if (is_string($etapas_utilizadas)) {
            $this->etapas_utilizadas = $etapas_utilizadas;
        } elseif (is_null($etapas_utilizadas)) {
            $this->etapas_utilizadas = null;
        }

        if (is_numeric($etapas_especificas)) {
            $this->etapas_especificas = $etapas_especificas;
        } elseif (is_null($etapas_especificas)) {
            $this->etapas_especificas = 0;
            $this->etapas_utilizadas = null;
        }

        if (is_array($anos_letivos)) {
            $this->anos_letivos = $anos_letivos;
        }

        if (is_numeric($aulas_por_semana)) {
            $this->aulas_por_semana = $aulas_por_semana;
        } elseif (is_null($aulas_por_semana)) {
            $this->aulas_por_semana = null;
        }
    }

    /**
     * Cria um novo registro.
     *
     * @return bool
     */
    public function cadastra()
    {
        if (is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola) && is_numeric($this->ref_cod_disciplina)) {
            $db = new clsBanco;

            $campos = '';
            $valores = '';
            $gruda = '';

            if (is_numeric($this->ref_ref_cod_serie)) {
                $campos .= "{$gruda}ref_ref_cod_serie";
                $valores .= "{$gruda}'{$this->ref_ref_cod_serie}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ref_ref_cod_escola)) {
                $campos .= "{$gruda}ref_ref_cod_escola";
                $valores .= "{$gruda}'{$this->ref_ref_cod_escola}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ref_cod_disciplina)) {
                $campos .= "{$gruda}ref_cod_disciplina";
                $valores .= "{$gruda}'{$this->ref_cod_disciplina}'";
                $gruda = ', ';
            }

            if (is_numeric($this->carga_horaria)) {
                $campos .= "{$gruda}carga_horaria";
                $valores .= "{$gruda}'{$this->carga_horaria}'";
                $gruda = ', ';
            } elseif (is_null($this->carga_horaria)) {
                $campos .= "{$gruda}carga_horaria";
                $valores .= "{$gruda}null";
                $gruda = ', ';
            }

            if (is_numeric($this->hora_falta)) {
                $campos .= "{$gruda}hora_falta";
                $valores .= "{$gruda}'{$this->hora_falta}'";
                $gruda = ', ';
            } elseif (is_null($this->hora_falta)) {
                $campos .= "{$gruda}hora_falta";
                $valores .= "{$gruda}null";
                $gruda = ', ';
            }

            if (is_numeric($this->etapas_especificas)) {
                $campos .= "{$gruda}etapas_especificas";
                $valores .= "{$gruda}'{$this->etapas_especificas}'";
                $gruda = ', ';
            }

            if (is_string($this->etapas_utilizadas)) {
                $campos .= "{$gruda}etapas_utilizadas";
                $valores .= "{$gruda}'{$this->etapas_utilizadas}'";
                $gruda = ', ';
            } elseif (is_null($this->etapas_utilizadas)) {
                $campos .= "{$gruda}etapas_utilizadas";
                $valores .= "{$gruda}null";
                $gruda = ', ';
            }

            if (is_array($this->anos_letivos)) {
                $campos .= "{$gruda}anos_letivos";
                $valores .= "{$gruda} " . Portabilis_Utils_Database::arrayToPgArray($this->anos_letivos) . ' ';
            }

            if (is_numeric($this->aulas_por_semana)) {
                $campos .= "{$gruda}aulas_por_semana";
                $valores .= "{$gruda}'{$this->aulas_por_semana}'";
                $gruda = ', ';
            } elseif (is_null($this->aulas_por_semana)) {
                $campos .= "{$gruda}aulas_por_semana";
                $valores .= "{$gruda}null";
                $gruda = ', ';
            }

            $campos .= "{$gruda}ativo";
            $valores .= "{$gruda}'1'";
            $gruda = ', ';

            $db->Consulta("INSERT INTO {$this->_tabela} ($campos) VALUES ($valores)");

            return true;
        }

        return false;
    }

    /**
     * Edita os dados de um registro.
     *
     * @return bool
     */
    public function edita()
    {
        if (is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola) && is_numeric($this->ref_cod_disciplina)) {
            $db = new clsBanco;
            $set = [];

            if (is_numeric($this->ativo)) {
                $set[] = "ativo = '{$this->ativo}'";
            }

            if (is_numeric($this->carga_horaria)) {
                $set[] = "carga_horaria = '{$this->carga_horaria}'";
            } elseif (is_null($this->carga_horaria)) {
                $set[] = 'carga_horaria = NULL';
            }

            if (is_numeric($this->hora_falta)) {
                $set[] = "hora_falta = '{$this->hora_falta}'";
            } elseif (is_null($this->hora_falta)) {
                $set[] = 'hora_falta = NULL';
            }

            if (is_numeric($this->etapas_especificas)) {
                $set[] = "etapas_especificas = '{$this->etapas_especificas}'";
            }

            if (is_string($this->etapas_utilizadas)) {
                $set[] = "etapas_utilizadas = '{$this->etapas_utilizadas}'";
            } elseif (is_null($this->etapas_utilizadas)) {
                $set[] = 'etapas_utilizadas = NULL';
            }

            if (is_array($this->anos_letivos)) {
                $set[] = 'anos_letivos = ' . Portabilis_Utils_Database::arrayToPgArray($this->anos_letivos) . ' ';
            }

            if (is_numeric($this->aulas_por_semana)) {
                $set[] = "aulas_por_semana = '{$this->aulas_por_semana}'";
            } elseif (is_null($this->aulas_por_semana)) {
                $set[] = 'aulas_por_semana = NULL';
            }

            $fields = implode(', ', $set);

            if ($fields) {
                $db->Consulta("UPDATE {$this->_tabela} SET $fields WHERE ref_ref_cod_serie = '{$this->ref_ref_cod_serie}' AND ref_ref_cod_escola = '{$this->ref_ref_cod_escola}' AND ref_cod_disciplina = '{$this->ref_cod_disciplina}'");

                return true;
            }
        }

        return false;
    }

    /**
     * Retorna uma lista de registros filtrados de acordo com os parâmetros.
     *
     * @return array
     *
     * @todo Refatorar o primeiro if, tabela referenciada não armazena mais os
     *   componentes curriculares
     */
    public function lista(
        $int_ref_ref_cod_serie = null,
        $int_ref_ref_cod_escola = null,
        $int_ref_cod_disciplina = null,
        $int_ativo = null,
        $boo_nome_disc = false,
        $int_etapa = null,
        $anoLetivo = null
    ) {
        $whereAnd = ' WHERE ';

        $campos = '';
        $join = '';

        $sql = "SELECT {$this->_campos_lista}{$campos} FROM {$this->_tabela}{$join}";
        $filtros = '';

        if (is_numeric($int_ref_ref_cod_serie)) {
            $filtros .= "{$whereAnd} ref_ref_cod_serie = '{$int_ref_ref_cod_serie}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_ref_cod_escola)) {
            $filtros .= "{$whereAnd} ref_ref_cod_escola = '{$int_ref_ref_cod_escola}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_cod_disciplina)) {
            $filtros .= "{$whereAnd} ref_cod_disciplina = '{$int_ref_cod_disciplina}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ativo)) {
            $filtros .= "{$whereAnd} escola_serie_disciplina.ativo = '{$int_ativo}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_etapa)) {
            $filtros .= "{$whereAnd} (case when escola_serie_disciplina.etapas_especificas = 1 then '{$int_etapa}' = ANY (string_to_array(escola_serie_disciplina.etapas_utilizadas,',')::int[]) else true end)";
            $whereAnd = ' AND ';
        }

        if (is_numeric($anoLetivo)) {
            $filtros .= "{$whereAnd} {$anoLetivo} = ANY (escola_serie_disciplina.anos_letivos) ";
            $whereAnd = ' AND ';
        }

        $db = new clsBanco;
        $countCampos = count(explode(',', $this->_campos_lista));
        $resultado = [];

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();

        $this->_total = $db->CampoUnico("SELECT COUNT(0) FROM {$this->_tabela}{$join} {$filtros}");

        $db->Consulta($sql);

        if ($countCampos > 1) {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();

                $tupla['_total'] = $this->_total;
                $resultado[] = $tupla;
            }
        } else {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }

        if (count($resultado)) {
            return $resultado;
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro.
     *
     * @return array|false
     */
    public function detalhe()
    {
        if (is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola) && is_numeric($this->ref_cod_disciplina)) {
            $db = new clsBanco;
            $db->Consulta("SELECT {$this->_todos_campos} FROM {$this->_tabela} WHERE ref_ref_cod_serie = '{$this->ref_ref_cod_serie}' AND ref_ref_cod_escola = '{$this->ref_ref_cod_escola}' AND ref_cod_disciplina = '{$this->ref_cod_disciplina}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro.
     *
     * @return array|false
     */
    public function existe()
    {
        if (is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola) && is_numeric($this->ref_cod_disciplina)) {
            $db = new clsBanco;
            $db->Consulta("SELECT 1 FROM {$this->_tabela} WHERE ref_ref_cod_serie = '{$this->ref_ref_cod_serie}' AND ref_ref_cod_escola = '{$this->ref_ref_cod_escola}' AND ref_cod_disciplina = '{$this->ref_cod_disciplina}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Exclui um registro.
     *
     * @return bool
     */
    public function excluir()
    {
        if (is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola) && is_numeric($this->ref_cod_disciplina)) {
            $db = new clsBanco;
            $db->Consulta("DELETE FROM {$this->_tabela} WHERE ref_ref_cod_serie = '{$this->ref_ref_cod_serie}' AND ref_ref_cod_escola = '{$this->ref_ref_cod_escola}' AND ref_cod_disciplina = '{$this->ref_cod_disciplina}'");

            return true;
        }

        return false;
    }

    /**
     * Exclui todos os registros referentes a um tipo de avaliação.
     */
    public function excluirTodos()
    {
        if (is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola)) {
            $db = new clsBanco;
            $db->Consulta("DELETE FROM {$this->_tabela} WHERE ref_ref_cod_serie = '{$this->ref_ref_cod_serie}' AND ref_ref_cod_escola = '{$this->ref_ref_cod_escola}'");

            return true;
        }

        return false;
    }

    public function diferente($disciplinas)
    {
        if (is_array($disciplinas) && is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola)) {
            $disciplina_in = '';
            $conc = '';

            foreach ($disciplinas as $disciplina) {
                for ($i = 0; $i < count($disciplina); $i++) {
                    $disciplina_in .= "{$conc}{$disciplina[$i]}";
                    $conc = ',';
                }
            }

            $db = new clsBanco;
            $db->Consulta("SELECT ref_cod_disciplina FROM {$this->_tabela} WHERE ref_ref_cod_serie = '{$this->ref_ref_cod_serie}' AND ref_ref_cod_escola = '{$this->ref_ref_cod_escola}' AND ref_cod_disciplina not in ({$disciplina_in})");

            $resultado = [];

            while ($db->ProximoRegistro()) {
                $resultado[] = $db->Tupla();
            }

            return $resultado;
        }

        return false;
    }

    public function excluirNaoSelecionados(array $listaComponentesSelecionados)
    {
        if (is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola)) {
            $componentesSelecionados = implode(',', $listaComponentesSelecionados);

            $db = new clsBanco;
            $db->Consulta("DELETE FROM {$this->_tabela} WHERE ref_ref_cod_serie = '{$this->ref_ref_cod_serie}' AND ref_ref_cod_escola = '{$this->ref_ref_cod_escola}' and ref_cod_disciplina not in ({$componentesSelecionados})");

            return true;
        }

        return false;
    }

    public function existeDependencia(array $listaComponentesSelecionados, bool $exclusao = false)
    {
        if (is_numeric($this->ref_ref_cod_serie) && is_numeric($this->ref_ref_cod_escola)) {
            $componentesSelecionados = implode(',', $listaComponentesSelecionados);

            $condicao = 'NOT IN';
            if ($exclusao) {
                $condicao = 'IN';
            }
            $db = new clsBanco;
            $sql = "SELECT EXISTS (SELECT 1
                                     FROM {$this->_tabela}
                                    WHERE ref_ref_cod_serie = '{$this->ref_ref_cod_serie}'
                                      AND ref_ref_cod_escola = '{$this->ref_ref_cod_escola}'
                                      AND ref_cod_disciplina {$condicao} ({$componentesSelecionados})
                                      AND EXISTS (SELECT 1
                                                    FROM pmieducar.disciplina_dependencia
                                                   WHERE disciplina_dependencia.ref_cod_disciplina {$condicao} ({$componentesSelecionados})
                                                     AND disciplina_dependencia.ref_cod_escola = {$this->_tabela}.ref_ref_cod_escola
                                                     AND disciplina_dependencia.ref_cod_serie = {$this->_tabela}.ref_ref_cod_serie))";

            return dbBool($db->CampoUnico($sql));
        }

        return false;
    }
}
