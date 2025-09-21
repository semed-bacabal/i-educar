<?php

return new class extends clsCadastro
{
    public $pessoa_logada;

    public $ref_cod_turma;

    public $ref_cod_serie;

    public $ref_cod_curso;

    public $ref_cod_escola;

    public $ref_cod_instituicao;

    public $cod_quadro_horario;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $data_cadastra;

    public $data_exclusao;

    public $ativo;

    public function Inicializar()
    {
        $retorno = 'Novo';

        $this->ref_cod_turma = $_GET['ref_cod_turma'];
        $this->ref_cod_serie = $_GET['ref_cod_serie'];
        $this->ref_cod_curso = $_GET['ref_cod_curso'];
        $this->ref_cod_escola = $_GET['ref_cod_escola'];
        $this->ref_cod_instituicao = $_GET['ref_cod_instituicao'];
        $this->cod_quadro_horario = $_GET['ref_cod_quadro_horario'];
        $this->ano = $_GET['ano'];

        if (is_numeric(value: $this->cod_quadro_horario)) {
            $obj_quadro_horario = new clsPmieducarQuadroHorario(cod_quadro_horario: $this->cod_quadro_horario);
            $det_quadro_horario = $obj_quadro_horario->detalhe();
            if ($det_quadro_horario) {
                // Passa todos os valores obtidos no registro para atributos do objeto
                foreach ($det_quadro_horario as $campo => $val) {
                    $this->$campo = $val;
                }

                $obj_permissoes = new clsPermissoes;

                if ($obj_permissoes->permissao_excluir(int_processo_ap: 641, int_idpes_usuario: $this->pessoa_logada, int_soma_nivel_acesso: 7)) {
                    $this->fexcluir = true;
                }

                $retorno = 'Editar';
            }
        }

        $obj_permissoes = new clsPermissoes;

        $obj_permissoes->permissao_cadastra(
            int_processo_ap: 641,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 7,
            str_pagina_redirecionar: "educar_quadro_horario_lst.php?ref_cod_turma={$this->ref_cod_turma}&ref_cod_serie={$this->ref_cod_serie}&ref_cod_curso={$this->ref_cod_curso}&ref_cod_escola={$this->ref_cod_escola}&ref_cod_instituicao={$this->ref_cod_instituicao}&ano={$this->ano}"
        );

        $this->url_cancelar = "educar_quadro_horario_lst.php?ref_cod_turma={$this->ref_cod_turma}&ref_cod_serie={$this->ref_cod_serie}&ref_cod_curso={$this->ref_cod_curso}&ref_cod_escola={$this->ref_cod_escola}&ref_cod_instituicao={$this->ref_cod_instituicao}&ano={$this->ano}";

        $this->nome_url_cancelar = 'Cancelar';

        $nomeMenu = $retorno == 'Editar' ? $retorno : 'Cadastrar';

        $this->breadcrumb(currentPage: $nomeMenu . ' quadro de horários', breadcrumbs: [
            url(path: 'intranet/educar_servidores_index.php') => 'Servidores',
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        if ($this->retorno == 'Editar') {
            $this->Excluir();
        }

        // primary keys
        $this->campoOculto(nome: 'cod_quadro_horario', valor: $this->cod_quadro_horario);

        $this->inputsHelper()->dynamic(helperNames: ['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
    }

    public function Novo()
    {
        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(
            int_processo_ap: 641,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 7,
            str_pagina_redirecionar: "educar_quadro_horario_lst.php?ref_cod_turma={$this->ref_cod_turma}&ref_cod_serie={$this->ref_cod_serie}&ref_cod_curso={$this->ref_cod_curso}&ref_cod_escola={$this->ref_cod_escola}&ref_cod_instituicao={$this->ref_cod_instituicao}&ano={$this->ano}"
        );

        $obj = new clsPmieducarQuadroHorario;
        $lista = $obj->lista(null, null, $this->pessoa_logada, $this->ref_cod_turma, null, null, null, null, 1, $this->ano);
        if ($lista) {
            echo '<script>alert(\'Quadro de Horário já cadastrado para esta turma\');</script>';

            return false;
        }

        $obj = new clsPmieducarQuadroHorario(
            ref_usuario_cad: $this->pessoa_logada,
            ref_cod_turma: $this->ref_cod_turma,
            ativo: 1,
            ano: $this->ano
        );

        $cadastrou = $obj->cadastra();

        if ($cadastrou) {
            $this->mensagem .= 'Cadastro efetuado com sucesso.<br>';
            $this->simpleRedirect(url: "educar_quadro_horario_lst.php?ref_cod_turma={$this->ref_cod_turma}&ref_cod_serie={$this->ref_cod_serie}&ref_cod_curso={$this->ref_cod_curso}&ref_cod_escola={$this->ref_cod_escola}&ref_cod_instituicao={$this->ref_cod_instituicao}&ano={$this->ano}&busca=S");
        }

        $this->mensagem = 'Cadastro não realizado.<br>';

        return false;
    }

    public function Editar() {}

    public function Excluir()
    {
        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_excluir(
            int_processo_ap: 641,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 7,
            str_pagina_redirecionar: "educar_quadro_horario_lst.php?ref_cod_turma={$this->ref_cod_turma}&ref_cod_serie={$this->ref_cod_serie}&ref_cod_curso={$this->ref_cod_curso}&ref_cod_escola={$this->ref_cod_escola}&ref_cod_instituicao={$this->ref_cod_instituicao}&ano={$this->ano}"
        );

        if (is_numeric(value: $this->cod_quadro_horario)) {
            $obj_horarios = new clsPmieducarQuadroHorarioHorarios(
                ref_cod_quadro_horario: $this->cod_quadro_horario,
                dia_semana: 1
            );

            if ($obj_horarios->excluirTodos()) {
                $obj_quadro = new clsPmieducarQuadroHorario(
                    cod_quadro_horario: $this->cod_quadro_horario,
                    ref_usuario_exc: $this->pessoa_logada
                );

                if ($obj_quadro->excluir()) {
                    $this->mensagem .= 'Exclusão efetuada com sucesso.<br>';
                    $this->simpleRedirect(url: "educar_quadro_horario_lst.php?ref_cod_turma={$this->ref_cod_turma}&ref_cod_serie={$this->ref_cod_serie}&ref_cod_curso={$this->ref_cod_curso}&ref_cod_escola={$this->ref_cod_escola}&ref_cod_instituicao={$this->ref_cod_instituicao}&ano={$this->ano}");
                }
            }
        }

        $this->mensagem = 'Exclusão não realizada.<br>';

        return false;
    }

    public function makeExtra()
    {
        return file_get_contents(filename: __DIR__ . '/scripts/extra/educar-quadro-horario-horarios-cad.js');
    }

    public function Formular()
    {
        $this->title = 'Servidores - Quadro de Horários';
        $this->processoAp = '641';
    }
};
