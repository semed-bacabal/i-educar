<?php

use App\Models\EmployeeWithdrawal;
use App\Models\WithdrawalReason;
use App\Services\FileService;
use App\Services\UrlPresigner;
use App\Support\View\Employee\EmployeeReturn;
use Illuminate\Support\Carbon;

return new class extends clsCadastro
{
    public $pessoa_logada;

    public $id;

    public $ref_cod_servidor;

    public $sequencial;

    public $ref_cod_instituicao;

    public $ref_cod_motivo_afastamento;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $data_cadastro;

    public $data_exclusao;

    public $data_retorno;

    public $data_saida;

    public $ativo;

    public $status;

    public $alocacao_array;

    public $parametros;

    public $dias_da_semana = [
        '' => 'Selecione',
        1 => 'Domingo',
        2 => 'Segunda',
        3 => 'Terça',
        4 => 'Quarta',
        5 => 'Quinta',
        6 => 'Sexta',
        7 => 'Sábado',
    ];

    /**
     * Implementação do método clsCadastro::Inicializar()
     *
     * @see ieducar/intranet/include/clsCadastro#Inicializar()
     */
    public function Inicializar()
    {
        $retorno = 'Novo';
        $this->status = clsCadastro::NOVO;

        $this->ref_cod_instituicao = $this->getQueryString('ref_cod_instituicao');
        $this->ref_cod_servidor = $this->getQueryString('ref_cod_servidor');
        $this->sequencial = $this->getQueryString('sequencial');
        $this->retornar_servidor = $this->getQueryString('retornar_servidor');

        $urlPermite = sprintf(
            'educar_servidor_det.php?cod_servidor=%s&ref_cod_instituicao=%s',
            $this->ref_cod_servidor,
            $this->ref_cod_instituicao
        );

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(635, $this->pessoa_logada, 7, $urlPermite);

        if (is_numeric($this->ref_cod_servidor) && is_numeric($this->sequencial) &&
            is_numeric($this->ref_cod_instituicao)) {
            $registro = EmployeeWithdrawal::query()
                ->where('ref_cod_servidor', $this->ref_cod_servidor)
                ->where('ref_ref_cod_instituicao', $this->ref_cod_instituicao)
                ->where('sequencial', $this->sequencial)
                ->first()?->toArray();

            if ($registro) {
                // Passa todos os valores obtidos no registro para atributos do objeto
                foreach ($registro as $campo => $val) {
                    $this->$campo = $val;
                }

                if ($this->data_retorno) {
                    $this->data_retorno = dataFromPgToBr($this->data_retorno);
                }

                if ($this->data_saida) {
                    $this->data_saida = dataFromPgToBr($this->data_saida);
                }

                $retorno = 'Editar';
                $this->status = clsCadastro::EDITAR;
            }
        }

        $this->url_cancelar = sprintf(
            'educar_servidor_det.php?cod_servidor=%s&ref_cod_instituicao=%s',
            $this->ref_cod_servidor,
            $this->ref_cod_instituicao
        );

        $this->nome_url_cancelar = 'Cancelar';

        $this->breadcrumb('Registro de afastamento do servidor', [
            url('intranet/educar_servidores_index.php') => 'Servidores',
        ]);

        return $retorno;
    }

    /**
     * Implementação do método clsCadastro::Gerar()
     *
     * @see ieducar/intranet/include/clsCadastro#Gerar()
     */
    public function Gerar()
    {
        $this->form_enctype = ' enctype=\'multipart/form-data\'';
        $this->campoOculto('id', $this->id);
        $this->campoOculto('ref_cod_servidor', $this->ref_cod_servidor);
        $this->campoOculto('sequencial', $this->sequencial);
        $this->campoOculto('ref_cod_instituicao', $this->ref_cod_instituicao);
        $this->campoOculto('retornar_servidor', $this->retornar_servidor);

        $opcoes = WithdrawalReason::query()
            ->orderBy('nm_motivo', 'ASC')
            ->pluck('nm_motivo', 'cod_motivo_afastamento')
            ->prepend('Selecione', '');

        if ($this->status == clsCadastro::NOVO || $this->retornar_servidor != EmployeeReturn::SIM) {
            $this->campoLista(
                'ref_cod_motivo_afastamento',
                'Motivo Afastamento',
                $opcoes,
                $this->ref_cod_motivo_afastamento
            );
        } elseif ($this->status == clsCadastro::EDITAR) {
            $this->campoLista(
                'ref_cod_motivo_afastamento',
                'Motivo Afastamento',
                $opcoes,
                $this->ref_cod_motivo_afastamento,
                '',
                false,
                '',
                '',
                true
            );
        }

        // Datas para registro
        // Se novo registro
        if ($this->status == clsCadastro::NOVO || $this->retornar_servidor != EmployeeReturn::SIM) {
            $this->campoData('data_saida', 'Data de Afastamento', $this->data_saida, true);
        }
        // Se edição, mostra a data de afastamento
        elseif ($this->status == clsCadastro::EDITAR) {
            $this->campoOculto('data_saida', $this->data_saida);
            $this->campoRotulo('data_saida', 'Data de Afastamento', $this->data_saida);
        }

        // Se edição, mostra campo para entrar com data de retornoc
        if ($this->retornar_servidor == EmployeeReturn::SIM || $this->data_retorno) {
            $this->campoData('data_retorno', 'Data de Retorno', $this->data_retorno, true);
        }

        $obj_servidor = new clsPmieducarServidor(
            $this->ref_cod_servidor,
            null,
            null,
            null,
            null,
            null,
            1,
            $this->ref_cod_instituicao
        );

        $det_servidor = $obj_servidor->detalhe();

        if ($det_servidor) {
            // Se for professor
            if ($obj_servidor->isProfessor() == true) {
                $obj = new clsPmieducarQuadroHorarioHorarios;

                // Pega a lista de aulas alocadas para este servidor
                $lista = $obj->lista(
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $this->ref_cod_instituicao,
                    null,
                    $this->ref_cod_servidor,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    1,
                    null,
                    true
                );

                if ($lista) {
                    // Passa todos os valores obtidos no registro para atributos do objeto
                    foreach ($lista as $val) {
                        $temp = [];
                        $temp['hora_inicial'] = $val['hora_inicial'];
                        $temp['hora_final'] = $val['hora_final'];
                        $temp['dia_semana'] = $val['dia_semana'];
                        $temp['ref_cod_escola'] = $val['ref_cod_escola'];
                        $temp['ref_cod_disciplina'] = $val['ref_cod_disciplina'];
                        $temp['ref_cod_substituto'] = $val['ref_servidor_substituto'];
                        $objTemp = new clsPmieducarSerie($val['ref_cod_serie']);
                        $detalheTemp = $objTemp->detalhe();
                        $temp['ref_cod_curso'] = $detalheTemp['ref_cod_curso'];
                        $this->alocacao_array[] = $temp;
                    }

                    if ($this->alocacao_array) {
                        $tamanho = count($this->alocacao_array);
                        $script = "<script>\nvar num_alocacao = {$tamanho};\n";
                        $script .= "var array_servidores = Array();\n";

                        foreach ($this->alocacao_array as $key => $alocacao) {
                            $script .= "array_servidores[{$key}] = new Array();\n";

                            $hora_ini = explode(':', $alocacao['hora_inicial']);
                            $hora_fim = explode(':', $alocacao['hora_final']);

                            $horas_utilizadas = ($hora_fim[0] - $hora_ini[0]);
                            $minutos_utilizados = ($hora_fim[1] - $hora_ini[1]);

                            $horas = sprintf('%02d', (int) $horas_utilizadas);
                            $minutos = sprintf('%02d', (int) $minutos_utilizados);

                            $str_horas_utilizadas = "{$horas}:{$minutos}";

                            $script .= "array_servidores[{$key}][0] = '{$str_horas_utilizadas}';\n";
                            $script .= "array_servidores[{$key}][1] = '';\n\n";

                            $obj_escola = new clsPmieducarEscola($alocacao['ref_cod_escola']);
                            $det_escola = $obj_escola->detalhe();
                            $det_escola = $det_escola['nome'];
                            $nm_dia_semana = $this->dias_da_semana[$alocacao['dia_semana']];

                            $obj_subst = new clsPessoa_($alocacao['ref_cod_substituto']);
                            $det_subst = $obj_subst->detalhe();

                            if ($this->status == clsCadastro::NOVO) {
                                $this->campoTextoInv(
                                    "dia_semana_{$key}_",
                                    '',
                                    $nm_dia_semana,
                                    8,
                                    8,
                                    false,
                                    false,
                                    true,
                                    '',
                                    '',
                                    '',
                                    '',
                                    'dia_semana'
                                );

                                $this->campoTextoInv(
                                    "hora_inicial_{$key}_",
                                    '',
                                    $alocacao['hora_inicial'],
                                    5,
                                    5,
                                    false,
                                    false,
                                    true,
                                    '',
                                    '',
                                    '',
                                    '',
                                    'ds_hora_inicial_'
                                );

                                $this->campoTextoInv(
                                    "hora_final_{$key}_",
                                    '',
                                    $alocacao['hora_final'],
                                    5,
                                    5,
                                    false,
                                    false,
                                    true,
                                    '',
                                    '',
                                    '',
                                    '',
                                    'ds_hora_final_'
                                );

                                $this->campoTextoInv(
                                    "ref_cod_escola_{$key}",
                                    '',
                                    $det_escola,
                                    30,
                                    255,
                                    false,
                                    false,
                                    true,
                                    '',
                                    '',
                                    '',
                                    '',
                                    'ref_cod_escola_'
                                );

                                $this->campoTextoInv(
                                    "ref_cod_servidor_substituto_{$key}_",
                                    '',
                                    $det_subst['nome'],
                                    30,
                                    255,
                                    false,
                                    false,
                                    false,
                                    '',
                                    "<span name=\"ref_cod_servidor_substituto\" id=\"ref_cod_servidor_substituicao_{$key}\"><img border='0'  onclick=\"pesquisa_valores_popless('educar_pesquisa_servidor_lst.php?campo1=ref_cod_servidor_substituto[{$key}]&campo2=ref_cod_servidor_substituto_{$key}_&ref_cod_instituicao={$this->ref_cod_instituicao}&dia_semana={$alocacao['dia_semana']}&hora_inicial={$alocacao['hora_inicial']}&hora_final={$alocacao['hora_final']}&ref_cod_servidor={$this->ref_cod_servidor}&professor=1&ref_cod_escola={$alocacao['ref_cod_escola']}&horario=S&ref_cod_disciplina={$alocacao['ref_cod_disciplina']}&ref_cod_curso={$alocacao['ref_cod_curso']}', 'nome')\" src=\"imagens/lupa.png\" ></span>",
                                    '',
                                    '',
                                    'ref_cod_servidor_substituto'
                                );
                            }

                            $this->campoOculto("dia_semana_{$key}", $alocacao['dia_semana']);
                            $this->campoOculto("hora_inicial_{$key}", $alocacao['hora_inicial']);
                            $this->campoOculto("hora_final_{$key}", $alocacao['hora_final']);
                            $this->campoOculto("ref_cod_escola_{$key}", $alocacao['ref_cod_escola']);
                            $this->campoOculto("ref_cod_servidor_substituto[{$key}]", $alocacao['ref_cod_substituto']);
                        }

                        $script .= "\n</script>";

                        // Print do Javascript
                        echo $script;
                    }
                }
            }
        }
        if ($this->retornar_servidor != EmployeeReturn::SIM) {
            if ($this->id == '') {
                $this->id = null;
            }
            $fileService = new FileService(new UrlPresigner);
            $files = $fileService->getFiles(EmployeeWithdrawal::find($this->id));
            $this->addHtml(view('uploads.upload', ['files' => $files])->render());
        }
    }

    public function Novo()
    {
        $this->data_saida = formatDateParse($this->data_saida, 'Y-m-d');
        if ($this->data_saida == null || $this->data_saida <= date('Y-m-d', strtotime('-1 year'))) {
            $this->data_saida = null;
            $this->mensagem = 'Data de Afastamento Inválida.<br>';

            return false;
        }
        $this->data_retorno = dataToBanco($this->data_retorno);

        $this->ref_cod_servidor = isset($_POST['ref_cod_servidor']) ? $_POST['ref_cod_servidor'] : null;

        $urlPermite = sprintf(
            'educar_servidor_det.php?cod_servidor=%d&ref_cod_instituicao=%d',
            $this->ref_cod_servidor,
            $this->ref_cod_instituicao
        );

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(635, $this->pessoa_logada, 7, $urlPermite);

        $withdrawal = new EmployeeWithdrawal;
        $withdrawal->ref_cod_servidor = $this->ref_cod_servidor;
        $withdrawal->ref_usuario_cad = $this->pessoa_logada;
        $withdrawal->ref_cod_motivo_afastamento = $this->ref_cod_motivo_afastamento;
        $withdrawal->data_retorno = $this->data_retorno ? formatDateParse($this->data_retorno, 'Y-m-d') : null;
        $withdrawal->data_saida = formatDateParse($this->data_saida, 'Y-m-d');
        $withdrawal->ref_ref_cod_instituicao = $this->ref_cod_instituicao;
        $withdrawal->sequencial = null;

        $cadastrou = $withdrawal->save();
        if ($cadastrou) {
            if (is_array($_POST['ref_cod_servidor_substituto'])) {
                /*
                 * Itera cada substituto e atualiza o quadro de horário com o código
                 * do servidor substituto, campos:
                 * - ref_cod_instituicao_substituto
                 * - ref_cod_servidor_substituto
                 */
                foreach ($_POST['ref_cod_servidor_substituto'] as $key => $valor) {
                    $ref_cod_servidor_substituto = $valor;
                    $ref_cod_escola = $_POST["ref_cod_escola_{$key}"];
                    $dia_semana = $_POST["dia_semana_{$key}"];
                    $hora_inicial = urldecode($_POST["hora_inicial_{$key}"]);
                    $hora_final = urldecode($_POST["hora_final_{$key}"]);

                    if (is_numeric($ref_cod_servidor_substituto) && is_numeric($ref_cod_escola) &&
                        is_numeric($dia_semana) && is_string($hora_inicial) &&
                        is_string($hora_final)
                    ) {
                        $obj_horarios = new clsPmieducarQuadroHorarioHorarios(
                            null,
                            null,
                            $ref_cod_escola,
                            null,
                            null,
                            null,
                            $this->ref_cod_instituicao,
                            $ref_cod_servidor_substituto,
                            $this->ref_cod_servidor,
                            $hora_inicial,
                            $hora_final,
                            null,
                            null,
                            1,
                            $dia_semana
                        );

                        $det_horarios = $obj_horarios->detalhe($ref_cod_escola);

                        $obj_horario = new clsPmieducarQuadroHorarioHorarios(
                            $det_horarios['ref_cod_quadro_horario'],
                            $det_horarios['ref_cod_serie'],
                            $det_horarios['ref_cod_escola'],
                            $det_horarios['ref_cod_disciplina'],
                            $det_horarios['sequencial'],
                            $det_horarios['ref_cod_instituicao_servidor'],
                            $det_horarios['ref_cod_instituicao_servidor'],
                            $ref_cod_servidor_substituto,
                            $this->ref_cod_servidor,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null
                        );

                        // Caso a atualização não tenha sucesso
                        if (!$obj_horario->edita()) {
                            $this->mensagem = 'Cadastro não realizado.<br>';

                            return false;
                        }
                    }
                }

                $this->mensagem .= 'Cadastro efetuado com sucesso.<br>';
                $this->simpleRedirect("educar_servidor_det.php?cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}");
            }
        } else {
            $this->mensagem = 'Cadastro não realizado.<br>';

            return false;
        }

        $fileService = new FileService(new UrlPresigner);

        if ($this->file_url) {
            $newFiles = json_decode($this->file_url);
            foreach ($newFiles as $file) {
                $fileService->saveFile(
                    $file->url,
                    $file->size,
                    $file->originalName,
                    $file->extension,
                    EmployeeWithdrawal::class,
                    $withdrawal->getKey()
                );
            }
        }

        $this->mensagem .= 'Cadastro efetuado com sucesso.<br>';
        $this->simpleRedirect("educar_servidor_det.php?cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}");
    }

    public function Editar()
    {
        $this->data_saida = formatDateParse(str_replace('%2F', '/', $this->data_saida), 'Y-m-d');
        if ($this->data_saida == null) {
            $this->data_saida = null;
            $this->mensagem = 'Data de Afastamento Inválida.<br>';

            return false;
        }

        $urlPermite = sprintf(
            'educar_servidor_det.php?cod_servidor=%d&ref_cod_instituicao=%d',
            $this->ref_cod_servidor,
            $this->ref_cod_instituicao
        );

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(635, $this->pessoa_logada, 7, $urlPermite);

        $returnDate = $this->data_retorno ? dataToBanco($this->data_retorno) : $this->data_retorno;

        if ($this->data_saida && $returnDate) {
            $returnDate = Carbon::createFromFormat('Y-m-d', $returnDate);
            $exitDate = Carbon::createFromFormat('Y-m-d', $this->data_saida);
            if (!$this->validateDates($exitDate, $returnDate)) {
                $this->mensagem = 'A data de retorno não pode ser inferior à data de afastamento.';

                return false;
            }
            $returnDate = $returnDate->format('Y-m-d');
        }

        $withdrawal = EmployeeWithdrawal::query()
            ->where('ref_cod_servidor', $this->ref_cod_servidor)
            ->where('ref_ref_cod_instituicao', $this->ref_cod_instituicao)
            ->where('sequencial', $this->sequencial)
            ->first();

        if (!is_null($this->ref_cod_motivo_afastamento)) {
            $withdrawal->ref_cod_motivo_afastamento = $this->ref_cod_motivo_afastamento;
        }
        $withdrawal->data_retorno = $returnDate;
        $withdrawal->data_saida = $this->data_saida;

        $editou = $withdrawal->save();
        if ($editou) {
            if (is_array($_POST['ref_cod_servidor_substituto'])) {
                foreach ($_POST['ref_cod_servidor_substituto'] as $key => $valor) {
                    $ref_cod_servidor_substituto = $valor;
                    $ref_cod_escola = $_POST["ref_cod_escola_{$key}"];
                    $dia_semana = $_POST["dia_semana_{$key}"];
                    $hora_inicial = urldecode($_POST["hora_inicial_{$key}"]);
                    $hora_final = urldecode($_POST["hora_final_{$key}"]);

                    if (is_numeric($ref_cod_servidor_substituto) && is_numeric($ref_cod_escola) &&
                        is_numeric($dia_semana) && is_string($hora_inicial) &&
                        is_string($hora_final)
                    ) {
                        $obj_horarios = new clsPmieducarQuadroHorarioHorarios(
                            null,
                            null,
                            $ref_cod_escola,
                            null,
                            null,
                            null,
                            $this->ref_cod_instituicao,
                            $ref_cod_servidor_substituto,
                            $this->ref_cod_servidor,
                            $hora_inicial,
                            $hora_final,
                            null,
                            null,
                            1,
                            $dia_semana
                        );

                        $det_horarios = $obj_horarios->detalhe($ref_cod_escola);

                        // Os valores NULL apagam os campos ref_cod_instituicao_ e
                        // ref_cod_servidor_ -substituto da tabela pmieducar.
                        // quadro_horario_horarios
                        $obj_horario = new clsPmieducarQuadroHorarioHorarios(
                            $det_horarios['ref_cod_quadro_horario'],
                            $det_horarios['ref_cod_serie'],
                            $det_horarios['ref_cod_escola'],
                            $det_horarios['ref_cod_disciplina'],
                            $det_horarios['sequencial'],
                            null,
                            $det_horarios['ref_cod_instituicao_servidor'],
                            null,
                            $this->ref_cod_servidor
                        );

                        if (!$obj_horario->edita()) {
                            $this->mensagem = 'Cadastro não realizado.<br>';

                            return false;
                        }
                    }
                }
            }

            $fileService = new FileService(new UrlPresigner);

            if ($this->file_url) {
                $newFiles = json_decode($this->file_url);
                foreach ($newFiles as $file) {
                    $fileService->saveFile(
                        $file->url,
                        $file->size,
                        $file->originalName,
                        $file->extension,
                        EmployeeWithdrawal::class,
                        $this->id
                    );
                }
            }

            if ($this->file_url_deleted) {
                $deletedFiles = explode(',', $this->file_url_deleted);
                $fileService->deleteFiles($deletedFiles);
            }

            $this->mensagem .= 'Edição efetuada com sucesso.<br>';
            $this->simpleRedirect("educar_servidor_det.php?cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}");
        }

        $this->mensagem = 'Edição não realizada.<br>';

        return false;
    }

    /**
     * Implementação do método clsCadastro::Excluir()
     *
     * @see ieducar/intranet/include/clsCadastro#Excluir()
     */
    public function Excluir()
    {
        $urlPermite = sprintf(
            'educar_servidor_det.php?cod_servidor=%d&ref_cod_instituicao=%d',
            $this->ref_cod_servidor,
            $this->ref_cod_instituicao
        );

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_excluir(635, $this->pessoa_logada, 7, $urlPermite);

        $withdrawal = EmployeeWithdrawal::query()
            ->where('ref_cod_servidor', $this->ref_cod_servidor)
            ->where('ref_ref_cod_instituicao', $this->ref_cod_instituicao)
            ->where('sequencial', $this->sequencial)
            ->first();

        $excluiu = $withdrawal->delete();

        if ($excluiu) {
            $this->mensagem .= 'Exclusão efetuada com sucesso.<br>';
            $this->simpleRedirect('educar_servidor_afastamento_lst.php');
        }

        $this->mensagem = 'Exclusão não realizada.<br>';

        return false;
    }

    public function makeExtra()
    {
        return file_get_contents(__DIR__ . '/scripts/extra/educar-servidor-afastamento-cad.js');
    }

    public function Formular()
    {
        $this->title = 'Servidores - Servidor Afastamento';
        $this->processoAp = '635';
    }

    private function validateDates(Carbon $exitDate, Carbon $returnDate)
    {
        return $returnDate->gte($exitDate);
    }
};
