<?php

use App\Models\LegacyExemptionType;

return new class extends clsDetalhe
{
    public $titulo;

    public $cod_tipo_dispensa;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nm_tipo;

    public $descricao;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $ref_cod_instituicao;

    public function Gerar()
    {
        $this->titulo = 'Tipo Dispensa - Detalhe';

        $this->cod_tipo_dispensa = $_GET['cod_tipo_dispensa'];

        $registro = LegacyExemptionType::find($this->cod_tipo_dispensa)?->getAttributes();
        if (!$registro) {
            $this->simpleRedirect('educar_tipo_dispensa_lst.php');
        }
        $obj_instituicao = new clsPmieducarInstituicao($registro['ref_cod_instituicao']);
        $obj_instituicao_det = $obj_instituicao->detalhe();
        $registro['ref_cod_instituicao'] = $obj_instituicao_det['nm_instituicao'];

        $obj_permissoes = new clsPermissoes;
        $nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);
        if ($nivel_usuario == 1) {
            if ($registro['ref_cod_instituicao']) {
                $this->addDetalhe(['Instituição', "{$registro['ref_cod_instituicao']}"]);
            }
        }
        if ($registro['nm_tipo']) {
            $this->addDetalhe(['Tipo Dispensa', "{$registro['nm_tipo']}"]);
        }
        if ($registro['descricao']) {
            $this->addDetalhe(['Descrição', "{$registro['descricao']}"]);
        }

        if ($obj_permissoes->permissao_cadastra(577, $this->pessoa_logada, 7)) {
            $this->url_novo = 'educar_tipo_dispensa_cad.php';
            $this->url_editar = "educar_tipo_dispensa_cad.php?cod_tipo_dispensa={$registro['cod_tipo_dispensa']}";
        }
        $this->url_cancelar = 'educar_tipo_dispensa_lst.php';
        $this->largura = '100%';

        $this->breadcrumb('Detalhe do tipo de dispensa', [
            url('intranet/educar_index.php') => 'Escola',
        ]);
    }

    public function Formular()
    {
        $this->title = 'Tipo Dispensa';
        $this->processoAp = '577';
    }
};
