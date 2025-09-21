<?php

use App\Models\LegacyExemptionType;

return new class extends clsListagem
{
    public $pessoa_logada;

    public $titulo;

    public $limite;

    public $offset;

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
        $this->titulo = 'Tipo Dispensa - Listagem';

        foreach ($_GET as $var => $val) { // passa todos os valores obtidos no GET para atributos do objeto
            $this->$var = ($val === '') ? null : $val;
        }

        $lista_busca = [
            'Tipo Dispensa',
        ];

        $obj_permissoes = new clsPermissoes;
        $nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);
        if ($nivel_usuario == 1) {
            $lista_busca[] = 'Instituição';
        }
        $this->addCabecalhos($lista_busca);

        // Filtros de Foreign Keys
        include 'include/pmieducar/educar_campo_lista.php';

        // outros Filtros
        $this->campoTexto('nm_tipo', 'Tipo Dispensa', $this->nm_tipo, 30, 255, false);

        // Paginador
        $this->limite = 20;

        $query = LegacyExemptionType::query()
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

                $lista_busca = [
                    "<a href=\"educar_tipo_dispensa_det.php?cod_tipo_dispensa={$registro['cod_tipo_dispensa']}\">{$registro['nm_tipo']}</a>",
                ];

                if ($nivel_usuario == 1) {
                    $lista_busca[] = "<a href=\"educar_tipo_dispensa_det.php?cod_tipo_dispensa={$registro['cod_tipo_dispensa']}\">{$registro['ref_cod_instituicao']}</a>";
                }
                $this->addLinhas($lista_busca);
            }
        }
        $this->addPaginador2('educar_tipo_dispensa_lst.php', $total, $_GET, $this->nome, $this->limite);

        if ($obj_permissoes->permissao_cadastra(577, $this->pessoa_logada, 7)) {
            $this->acao = 'go("educar_tipo_dispensa_cad.php")';
            $this->nome_acao = 'Novo';
        }
        $this->largura = '100%';

        $this->breadcrumb('Listagem de tipos de dispensa', [
            url('intranet/educar_index.php') => 'Escola',
        ]);
    }

    public function Formular()
    {
        $this->title = 'Tipo Dispensa';
        $this->processoAp = '577';
    }
};
