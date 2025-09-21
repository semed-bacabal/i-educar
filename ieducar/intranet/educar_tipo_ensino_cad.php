<?php

use App\Models\LegacyEducationType;

return new class extends clsCadastro
{
    public $pessoa_logada;

    public $cod_tipo_ensino;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nm_tipo;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $atividade_complementar;

    public $ref_cod_instituicao;

    public function Inicializar()
    {
        $retorno = 'Novo';

        // ** Verificacao de permissao para exclusao
        $obj_permissao = new clsPermissoes;

        $obj_permissao->permissao_cadastra(558, $this->pessoa_logada, 7, 'educar_tipo_ensino_lst.php');
        // **

        $this->cod_tipo_ensino = $_GET['cod_tipo_ensino'];

        if (is_numeric($this->cod_tipo_ensino)) {
            $registro = LegacyEducationType::find($this->cod_tipo_ensino)?->getAttributes();
            if (!$registro) {
                $this->simpleRedirect('educar_tipo_ensino_lst.php');
            }

            if (!$registro['ativo']) {
                $this->simpleRedirect('educar_tipo_ensino_lst.php');
            }

            if ($registro) {
                foreach ($registro as $campo => $val) {  // passa todos os valores obtidos no registro para atributos do objeto
                    $this->$campo = $val;
                }

                // ** verificao de permissao para exclusao
                $this->fexcluir = $obj_permissao->permissao_excluir(558, $this->pessoa_logada, 7);
                // **

                $retorno = 'Editar';
            }
            $this->atividade_complementar = dbBool($this->atividade_complementar);
        }
        $this->url_cancelar = ($retorno == 'Editar') ? "educar_tipo_ensino_det.php?cod_tipo_ensino={$registro['cod_tipo_ensino']}" : 'educar_tipo_ensino_lst.php';

        $nomeMenu = $retorno == 'Editar' ? $retorno : 'Cadastrar';

        $this->breadcrumb($nomeMenu . ' tipo de ensino', [
            url('intranet/educar_index.php') => 'Escola',
        ]);

        $this->nome_url_cancelar = 'Cancelar';

        return $retorno;
    }

    public function Gerar()
    {
        // primary keys
        $this->campoOculto('cod_tipo_ensino', $this->cod_tipo_ensino);

        $get_escola = false;
        $obrigatorio = true;
        include 'include/pmieducar/educar_campo_lista.php';

        $this->campoTexto('nm_tipo', 'Tipo de Ensino', $this->nm_tipo, 30, 255, true);
        $this->campoCheck('atividade_complementar', 'Atividade complementar', $this->atividade_complementar);
    }

    public function Novo()
    {
        $this->atividade_complementar = is_null($this->atividade_complementar) ? false : true;

        $object = new LegacyEducationType;
        $object->ref_usuario_cad = $this->pessoa_logada;
        $object->nm_tipo = $this->nm_tipo;
        $object->ref_cod_instituicao = $this->ref_cod_instituicao;
        $object->atividade_complementar = $this->atividade_complementar;

        if ($object->save()) {
            $this->mensagem .= 'Cadastro efetuado com sucesso.<br>';
            $this->simpleRedirect('educar_tipo_ensino_lst.php');
        }

        $this->mensagem = 'Cadastro não realizado.<br>';

        return false;
    }

    public function Editar()
    {
        $this->atividade_complementar = is_null($this->atividade_complementar) ? false : true;

        $object = LegacyEducationType::find($this->cod_tipo_ensino);
        $object->ativo = 1;
        $object->ref_usuario_exc = $this->pessoa_logada;
        $object->nm_tipo = $this->nm_tipo;
        $object->ref_cod_instituicao = $this->ref_cod_instituicao;
        $object->atividade_complementar = $this->atividade_complementar;

        if ($object->save()) {
            $this->mensagem .= 'Edição efetuada com sucesso.<br>';
            $this->simpleRedirect('educar_tipo_ensino_lst.php');
        }

        $this->mensagem = 'Edição não realizada.<br>';

        return false;
    }

    public function Excluir()
    {
        $object = LegacyEducationType::find($this->cod_tipo_ensino);
        $object->ativo = 0;
        $object->ref_usuario_exc = $this->pessoa_logada;

        if ($object->save()) {
            $this->mensagem .= 'Exclusão efetuada com sucesso.<br>';
            $this->simpleRedirect('educar_tipo_ensino_lst.php');
        }

        $this->mensagem = 'Exclusão não realizada.<br>';

        return false;
    }

    public function Formular()
    {
        $this->title = 'Tipo Ensino';
        $this->processoAp = '558';
    }
};
