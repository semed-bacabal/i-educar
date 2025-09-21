<?php

use App\Models\LegacyEducationType;

return new class extends clsListagem
{
    public $pessoa_logada;

    public $titulo;

    public $limite;

    public $offset;

    public $cod_tipo_ensino;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nm_tipo;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $ref_cod_instituicao;

    public function Gerar()
    {
        $this->titulo = 'Tipo Ensino - Listagem';

        foreach ($_GET as $var => $val) { // passa todos os valores obtidos no GET para atributos do objeto
            $this->$var = ($val === '') ? null : $val;
        }

        $get_escola = false;
        include 'include/pmieducar/educar_campo_lista.php';
        $obj_permissao = new clsPermissoes;
        $nivel_usuario = $obj_permissao->nivel_acesso($this->pessoa_logada);

        switch ($nivel_usuario) {
            case 1:
                $this->addCabecalhos([
                    'Tipo Ensino',
                    'Instituição',
                ]);
                break;

            default:
                $this->addCabecalhos([
                    'Tipo Ensino',
                ]);
                break;
        }

        // Filtros de Foreign Keys

        // outros Filtros
        $this->campoTexto('nm_tipo', 'Nome Tipo', $this->nm_tipo, 30, 255, false);

        // Paginador
        $this->limite = 20;

        $query = LegacyEducationType::query()
            ->where('ativo', 1)
            ->orderBy('nm_tipo', 'ASC');

        if (is_string($this->nm_tipo)) {
            $query->where('nm_tipo', 'ilike', '%' . $this->nm_tipo . '%');
        }

        if (is_numeric($this->ref_cod_instituicao)) {
            $query->where('ref_cod_instituicao', $this->ref_cod_instituicao);
        }

        $result = $query->paginate($this->limite, pageName: 'pagina_'.$this->nome);

        $lista = $result->items();
        $total = $result->total();

        // monta a lista
        if (is_array($lista) && count($lista)) {
            foreach ($lista as $registro) {
                $obj_cod_instituicao = new clsPmieducarInstituicao($registro['ref_cod_instituicao']);
                $obj_cod_instituicao_det = $obj_cod_instituicao->detalhe();
                $registro['ref_cod_instituicao'] = $obj_cod_instituicao_det['nm_instituicao'];

                switch ($nivel_usuario) {
                    case 1:
                        $this->addLinhas([
                            "<a href=\"educar_tipo_ensino_det.php?cod_tipo_ensino={$registro['cod_tipo_ensino']}\">{$registro['nm_tipo']}</a>",
                            "<a href=\"educar_tipo_ensino_det.php?cod_tipo_ensino={$registro['cod_tipo_ensino']}\">{$registro['ref_cod_instituicao']}</a>",
                        ]);
                        break;

                    default:
                        $this->addLinhas([
                            "<a href=\"educar_tipo_ensino_det.php?cod_tipo_ensino={$registro['cod_tipo_ensino']}\">{$registro['nm_tipo']}</a>",
                        ]);
                        break;
                }
            }
        }

        // ** Verificacao de permissao para exclusao

        if ($obj_permissao->permissao_cadastra(558, $this->pessoa_logada, 3)) {
            $this->acao = 'go("educar_tipo_ensino_cad.php")';
            $this->nome_acao = 'Novo';
        }
        // **

        $this->addPaginador2('educar_tipo_ensino_lst.php', $total, $_GET, $this->nome, $this->limite);
        $this->largura = '100%';

        $this->breadcrumb('Listagem de tipos de ensino', [
            url('intranet/educar_index.php') => 'Escola',
        ]);
    }

    public function Formular()
    {
        $this->title = 'Tipo Ensino';
        $this->processoAp = '558';
    }
};
