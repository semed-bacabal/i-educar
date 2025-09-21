<?php

use App\Models\LegacyRegimeType;

return new class extends clsCadastro
{
    public $pessoa_logada;

    public $cod_tipo_regime;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nm_tipo;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $ref_cod_instituicao;

    public function Inicializar()
    {
        $retorno = 'Novo';

        $this->cod_tipo_regime = $_GET['cod_tipo_regime'];

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(568, $this->pessoa_logada, 3, 'educar_tipo_regime_lst.php');

        if (is_numeric($this->cod_tipo_regime)) {
            $registro = LegacyRegimeType::findOrFail($this->cod_tipo_regime)?->getAttributes();
            if ($registro) {
                foreach ($registro as $campo => $val) {  // passa todos os valores obtidos no registro para atributos do objeto
                    $this->$campo = $val;
                }

                $this->fexcluir = $obj_permissoes->permissao_excluir(568, $this->pessoa_logada, 3);
                $retorno = 'Editar';
            }
        }
        $this->nome_url_cancelar = 'Cancelar';
        $this->script_cancelar = 'window.parent.fechaExpansivel("div_dinamico_"+(parent.DOM_divs.length-1));';

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('cod_tipo_regime', $this->cod_tipo_regime);

        if ($_GET['precisa_lista']) {
            $get_escola = false;
            $obrigatorio = true;
            include 'include/pmieducar/educar_campo_lista.php';
        } else {
            $this->campoOculto('ref_cod_instituicao', $this->ref_cod_instituicao);
        }
        $this->campoTexto('nm_tipo', 'Nome Tipo', $this->nm_tipo, 30, 255, true);
    }

    public function Novo()
    {
        $type = new LegacyRegimeType;
        $type->ref_usuario_exc = $this->pessoa_logada;
        $type->ref_usuario_cad = $this->pessoa_logada;
        $type->nm_tipo = $this->nm_tipo;
        $type->ref_cod_instituicao = $this->ref_cod_instituicao;

        if ($type->save()) {
            echo "<script>
                        if (parent.document.getElementById('ref_cod_tipo_regime').disabled)
                            parent.document.getElementById('ref_cod_tipo_regime').options[0] = new Option('Selecione um tipo de regime', '', false, false);
                        parent.document.getElementById('ref_cod_tipo_regime').options[parent.document.getElementById('ref_cod_tipo_regime').options.length] = new Option('$this->nm_tipo', '$type->cod_tipo_regime', false, false);
                        parent.document.getElementById('ref_cod_tipo_regime').value = '$type->cod_tipo_regime';
                        parent.document.getElementById('ref_cod_tipo_regime').disabled = false;
                        window.parent.fechaExpansivel('div_dinamico_'+(parent.DOM_divs.length-1));
                    </script>";
            exit();
        }

        $this->mensagem = 'Cadastro não realizado.<br>';

        return false;
    }

    public function Editar() {}

    public function Excluir() {}

    public function Formular()
    {
        $this->title = 'Tipo Regime';
        $this->processoAp = '568';
        $this->renderMenu = false;
        $this->renderMenuSuspenso = false;
    }
};
