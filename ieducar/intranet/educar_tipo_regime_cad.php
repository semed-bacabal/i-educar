<?php

use App\Models\LegacyCourse;
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

                // ** verificao de permissao para exclusao
                $this->fexcluir = $obj_permissoes->permissao_excluir(568, $this->pessoa_logada, 3);
                // **
                $retorno = 'Editar';
            }
        }
        $this->url_cancelar = ($retorno == 'Editar') ? "educar_tipo_regime_det.php?cod_tipo_regime={$registro['cod_tipo_regime']}" : 'educar_tipo_regime_lst.php';

        $nomeMenu = $retorno == 'Editar' ? $retorno : 'Cadastrar';

        $this->breadcrumb($nomeMenu . ' tipo de regime', [
            url('intranet/educar_index.php') => 'Escola',
        ]);

        $this->nome_url_cancelar = 'Cancelar';

        return $retorno;
    }

    public function Gerar()
    {
        // primary keys
        $this->campoOculto('cod_tipo_regime', $this->cod_tipo_regime);

        $get_escola = false;
        $obrigatorio = true;
        include 'include/pmieducar/educar_campo_lista.php';
        // text
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
            $this->mensagem .= 'Cadastro efetuado com sucesso.<br>';
            $this->simpleRedirect('educar_tipo_regime_lst.php');
        }

        $this->mensagem = 'Cadastro não realizado.<br>';

        return false;
    }

    public function Editar()
    {
        $type = LegacyRegimeType::findOrFail($this->cod_tipo_regime);
        $type->ref_usuario_exc = $this->pessoa_logada;
        $type->ref_usuario_cad = $this->pessoa_logada;
        $type->nm_tipo = $this->nm_tipo;
        $type->data_exclusao = now();
        $type->ativo = 1;
        $type->ref_cod_instituicao = $this->ref_cod_instituicao;

        if ($type->save()) {
            $this->mensagem .= 'Edição efetuada com sucesso.<br>';
            $this->simpleRedirect('educar_tipo_regime_lst.php');
        }

        $this->mensagem = 'Edição não realizada.<br>';

        return false;
    }

    public function Excluir()
    {
        $count = LegacyCourse::query()
            ->where('ref_cod_tipo_regime', $this->cod_tipo_regime)
            ->count();

        if ($count > 0) {
            $this->mensagem = 'Você não pode excluir esse Tipo de Regime, pois ele possui vínculo com Curso(s).<br>';

            return false;
        }

        $type = LegacyRegimeType::findOrFail($this->cod_tipo_regime);
        $type->ref_usuario_exc = $this->pessoa_logada;
        $type->ativo = 0;

        if ($type->save()) {
            $this->mensagem .= 'Exclusão efetuada com sucesso.<br>';
            $this->simpleRedirect('educar_tipo_regime_lst.php');
        }

        $this->mensagem = 'Exclusão não realizada.<br>';

        return false;
    }

    public function Formular()
    {
        $this->title = 'Tipo Regime';
        $this->processoAp = '568';
    }
};
