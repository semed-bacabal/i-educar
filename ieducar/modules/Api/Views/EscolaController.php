<?php

use App\Models\LegacySchool;

class EscolaController extends ApiCoreController
{
    protected $_processoAp = 561;

    protected $_nivelAcessoOption = App_Model_NivelAcesso::SOMENTE_ESCOLA;

    protected function canChange()
    {
        return true;
    }

    protected function loadEscolaInepId($escolaId)
    {
        $dataMapper = $this->getDataMapperFor('educacenso', 'escola');
        $entity = $this->tryGetEntityOf($dataMapper, $escolaId);

        return is_null($entity) ? null : $entity->get('escolaInep');
    }

    protected function createUpdateOrDestroyEducacensoEscola($escolaId)
    {
        $dataMapper = $this->getDataMapperFor('educacenso', 'escola');

        $result = $this->deleteEntityOf($dataMapper, $escolaId);
        if (!empty($this->getRequest()->escola_inep_id)) {
            $data = [
                'escola' => $escolaId,
                'escolaInep' => $this->getRequest()->escola_inep_id,

                // campos deprecados?
                'fonte' => 'fonte',
                'nomeInep' => '-',

                // always setting now...
                'created_at' => 'NOW()',
            ];

            $entity = $this->getOrCreateEntityOf($dataMapper, $escolaId);
            $entity->setOptions($data);

            $result = $this->saveEntity($dataMapper, $entity);
        }

        return $result;
    }

    protected function get()
    {
        if ($this->canGet()) {
            $id = $this->getRequest()->id;

            $escola = [];
            $escola['escola_inep_id'] = $this->loadEscolaInepId($id);

            return $escola;
        }
    }

    protected function put()
    {
        $id = $this->getRequest()->id;

        if ($this->canPut()) {
            $this->createUpdateOrDestroyEducacensoEscola($id);

            $this->messenger->append('Cadastro alterado com sucesso', 'success', false, 'error');
        } else {
            $this->messenger->append(
                'Aparentemente o cadastro não pode ser alterado, por favor, verifique.',
                'error',
                false,
                'error'
            );
        }

        return ['id' => $id];
    }

    protected function canGetEtapasPorEscola()
    {
        return $this->validatesPresenceOf('instituicao_id');
    }

    protected function formatResourceValue($resource)
    {
        $nome = $this->toUtf8($resource['name'], ['transform' => true]);

        return $nome;
    }

    protected function canGetServidoresDisciplinasTurmas()
    {
        return $this->validatesPresenceOf('ano') && $this->validatesPresenceOf('instituicao_id');
    }

    protected function sqlsForStringSearch()
    {
        $sqls = [];

        $sqls[] = 'SELECT e.cod_escola as id, j.fantasia as name
               FROM pmieducar.escola e, cadastro.juridica j
              WHERE j.idpes = e.ref_idpes
                AND e.ativo = 1
                AND j.fantasia ILIKE \'%\'||$1||\'%\'
                LIMIT 8';

        $sqls[] = 'SELECT e.cod_escola as id, ec.nm_escola as name
               FROM pmieducar.escola e, pmieducar.escola_complemento ec
              WHERE e.cod_escola = ec.ref_cod_escola
                AND e.ativo = 1
                AND ec.nm_escola ILIKE \'%\'||$1||\'%\'
                LIMIT 8';

        return $sqls;
    }

    protected function getEtapasPorEscola()
    {
        if ($this->canGetEtapasPorEscola()) {
            $ano = $this->getRequest()->ano ? $this->getRequest()->ano : 0;
            $escola = $this->getRequest()->escola;

            $where = '';

            if ($escola) {
                if (is_array($escola)) {
                    $escola = implode(',', $escola);
                }

                $where = " AND eal.ref_cod_escola in ({$escola})";
            }

            $sql = "
                select distinct
                    ref_cod_escola as escola_id,
                    ano as ano,
                    m.nm_tipo as descricao,
                    andamento as ano_em_aberto
                from pmieducar.escola_ano_letivo eal
                inner join pmieducar.ano_letivo_modulo alm
                    on true
                    and alm.ref_ano = eal.ano
                    and alm.ref_ref_cod_escola = eal.ref_cod_escola
                inner join pmieducar.modulo m
                    on true
                    and m.cod_modulo = alm.ref_cod_modulo
                where true
                    and (
                        case when $1 = 0 then
                            (
                                andamento = 1
                                or
                                ano in (
                                    select ano
                                    from pmieducar.escola_ano_letivo
                                    where ref_cod_escola = eal.ref_cod_escola
                                    order by ano desc
                                    limit 2
                                )
                            )
                        else
                            ano = $1
                        end
                    )
                {$where}
                order by ref_cod_escola, ano
            ";

            $anosLetivos = $this->fetchPreparedQuery($sql, [$ano]);

            $attrs = ['escola_id', 'ano', 'descricao', 'ano_em_aberto'];
            $anosLetivos = Portabilis_Array_Utils::filterSet($anosLetivos, $attrs);

            $anosLetivos = array_map(function ($ano) {
                $ano['ano_em_aberto'] = $ano['ano_em_aberto'] == 1;

                return $ano;
            }, $anosLetivos);

            foreach ($anosLetivos as $index => $anoLetivo) {
                $anosLetivos[$index] = array_merge($anosLetivos[$index], $this->getEtapasAnoEscola($anoLetivo['ano'], $anoLetivo['escola_id']));
                $anosLetivos[$index] = array_merge($anosLetivos[$index], $this->getEtapasTurmasAnoEscola($anoLetivo['ano'], $anoLetivo['escola_id']));
            }

            return ['escolas' => $anosLetivos];
        }
    }

    private function getEtapasAnoEscola($ano, $escola)
    {
        $sql = 'SELECT sequencial AS etapa,
                       data_inicio,
                       data_fim,
                       dias_letivos
                  FROM pmieducar.ano_letivo_modulo
                 WHERE ref_ano = $1
                   AND ref_ref_cod_escola = $2
              ORDER BY sequencial';

        $etapas = [];
        $etapas = $this->fetchPreparedQuery($sql, [$ano, $escola]);
        $attrs = ['etapa', 'data_inicio', 'data_fim', 'dias_letivos'];
        $etapas = Portabilis_Array_Utils::filterSet($etapas, $attrs);

        return ['etapas' => $etapas];
    }

    private function getModuloDaEscola($ano, $escola)
    {
        $sql = '
            SELECT max(ref_cod_modulo) as modulo
            FROM pmieducar.ano_letivo_modulo
            WHERE ref_ano = $1
            AND ref_ref_cod_escola = $2
        ';
        $modulo = $this->fetchPreparedQuery($sql, [$ano, $escola], false, 'first-line');

        return $modulo['modulo'];
    }

    protected function getEtapasDaEscolaPorAno()
    {
        if ($this->canGetEtapasDaEscolaPorAno()) {
            $ano = $this->getRequest()->ano;
            $escolaId = $this->getRequest()->escola_id;
            $dadosDasEtapas = $this->getEtapasAnoEscola($ano, $escolaId);
            $dadosDasEtapas['modulo'] = $this->getModuloDaEscola($ano, $escolaId);

            return $dadosDasEtapas;
        }
    }

    protected function canGetEtapasDaEscolaPorAno()
    {
        return $this->validatesPresenceOf('ano') && $this->validatesPresenceOf('escola_id');
    }

    private function getEtapasTurmasAnoEscola($ano, $escola)
    {
        $sql_turmas = 'SELECT DISTINCT tm.ref_cod_turma as turma_id, m.nm_tipo as descricao
              FROM pmieducar.turma_modulo tm
              INNER JOIN pmieducar.turma t ON (tm.ref_cod_turma = t.cod_turma)
              INNER JOIN pmieducar.curso c on (c.cod_curso = t.ref_cod_curso)
              inner join pmieducar.modulo m
              on m.cod_modulo = tm.ref_cod_modulo
            WHERE t.ano = $1 and t.ref_ref_cod_escola = $2 and c.padrao_ano_escolar = 0 and t.ativo = 1
          ORDER BY tm.ref_cod_turma';

        $turmas = $this->fetchPreparedQuery($sql_turmas, [$ano, $escola]);
        $attrs_turmas = ['turma_id', 'descricao'];
        $turmas = Portabilis_Array_Utils::filterSet($turmas, $attrs_turmas);

        foreach ($turmas as $key => $turma) {
            $turmas[$key] = array_merge($turmas[$key], $this->getEtapasTurma($ano, $escola, $turma['turma_id']));
        }

        return ['etapas_de_turmas' => $turmas];
    }

    private function getEtapasTurma($ano, $escola, $turma)
    {
        $sql_etapas = 'SELECT tm.sequencial AS etapa,
                              tm.data_inicio,
                              tm.data_fim
                         FROM pmieducar.turma_modulo AS tm
                   INNER JOIN pmieducar.turma AS t
                           ON tm.ref_cod_turma = t.cod_turma
                        WHERE t.ano = $1
                          AND t.ref_ref_cod_escola = $2
                          AND tm.ref_cod_turma = $3
                     ORDER BY tm.ref_cod_turma, tm.sequencial';

        $etapas = [];
        $etapas = $this->fetchPreparedQuery($sql_etapas, [$ano, $escola, $turma]);
        $attrs_etapas = ['etapa', 'data_inicio', 'data_fim'];
        $etapas = Portabilis_Array_Utils::filterSet($etapas, $attrs_etapas);

        return ['etapas' => $etapas];
    }

    public function _getQtdAlunosFila($escolaId)
    {
        $sql = 'SELECT count(1) as qtd
            FROM pmieducar.matricula
            WHERE ano = $1
            AND ref_ref_cod_escola = $2
            AND ref_cod_curso = $3
            AND ref_ref_cod_serie = $4
            AND turno_pre_matricula = $5
            AND aprovado = 11 ';

        return (int) Portabilis_Utils_Database::selectField(
            $sql,
            [
                $this->getRequest()->ano,
                $escolaId,
                $this->getRequest()->curso_id, $this->getRequest()->serie_id,
                $this->getRequest()->turma_turno_id,
            ]
        );
    }

    public function _getQtdMatriculaTurno($escolaId)
    {
        $obj_mt = new clsPmieducarMatriculaTurma;

        return count(array_filter(($obj_mt->lista(
            $int_ref_cod_matricula = null,
            $int_ref_cod_turma = null,
            $int_ref_usuario_exc = null,
            $int_ref_usuario_cad = null,
            $date_data_cadastro_ini = null,
            $date_data_cadastro_fim = null,
            $date_data_exclusao_ini = null,
            $date_data_exclusao_fim = null,
            $int_ativo = 1,
            $int_ref_cod_serie = $this->getRequest()->serie_id,
            $int_ref_cod_curso = $this->getRequest()->curso_id,
            $int_ref_cod_escola = $escolaId,
            $int_ref_cod_instituicao = $this->getRequest()->instituicao_id,
            $int_ref_cod_aluno = null,
            $mes = null,
            $aprovado = null,
            $mes_menor_que = null,
            $int_sequencial = null,
            $int_ano_matricula = $this->getRequest()->ano,
            $tem_avaliacao = null,
            $bool_get_nome_aluno = false,
            $bool_aprovados_reprovados = null,
            $int_ultima_matricula = null,
            $bool_matricula_ativo = true,
            $bool_escola_andamento = true,
            $mes_matricula_inicial = false,
            $get_serie_mult = false,
            $int_ref_cod_serie_mult = null,
            $int_semestre = null,
            $pegar_ano_em_andamento = false,
            $parar = null,
            $diario = false,
            $int_turma_turno_id = $this->getRequest()->turma_turno_id,
            $int_ano_turma = $this->getRequest()->ano
        ))));
    }

    public function _getMaxAlunoTurno($escolaId)
    {
        $obj_t = new clsPmieducarTurma;
        $det_t = $obj_t->detalhe();

        $lista_t = $obj_t->lista(
            $int_cod_turma = null,
            $int_ref_usuario_exc = null,
            $int_ref_usuario_cad = null,
            $int_ref_ref_cod_serie = $this->getRequest()->serie_id,
            $int_ref_ref_cod_escola = $escolaId,
            $int_ref_cod_infra_predio_comodo = null, // TODO: remover no futuro
            $str_nm_turma = null,
            $str_sgl_turma = null,
            $int_max_aluno = null,
            $int_multiseriada = null,
            $date_data_cadastro_ini = null,
            $date_data_cadastro_fim = null,
            $date_data_exclusao_ini = null,
            $date_data_exclusao_fim = null,
            $int_ativo = 1,
            $int_ref_cod_turma_tipo = null,
            $time_hora_inicial_ini = null,
            $time_hora_inicial_fim = null,
            $time_hora_final_ini = null,
            $time_hora_final_fim = null,
            $time_hora_inicio_intervalo_ini = null,
            $time_hora_inicio_intervalo_fim = null,
            $time_hora_fim_intervalo_ini = null,
            $time_hora_fim_intervalo_fim = null,
            $int_ref_cod_curso = $this->getRequest()->curso_id,
            $int_ref_cod_instituicao = null,
            $int_ref_cod_regente = null,
            $int_ref_cod_instituicao_regente = null,
            $int_ref_ref_cod_escola_mult = null,
            $int_ref_ref_cod_serie_mult = null,
            $int_qtd_min_alunos_matriculados = null,
            $bool_verifica_serie_multiseriada = false,
            $bool_tem_alunos_aguardando_nota = null,
            $visivel = null,
            $turma_turno_id = $this->getRequest()->turma_turno_id,
            $tipo_boletim = null,
            $ano = $this->getRequest()->ano,
            $somenteAnoLetivoEmAndamento = false
        );

        $max_aluno_turmas = 0;

        foreach ($lista_t as $reg) {
            $max_aluno_turmas += $reg['max_aluno'];
        }

        return $max_aluno_turmas;
    }

    protected function getInformacaoEscolas()
    {
        $sql = ' SELECT escola.cod_escola as cod_escola,
          juridica.fantasia as nome,
          addresses.postal_code as cep,
          addresses.number as numero,
          addresses.complement as complemento,
          addresses.address as logradouro,
          addresses.neighborhood as bairro,
          addresses.city as municipio,
          addresses.state_abbreviation as uf,
          addresses.country as pais,
          pessoa.email as email,
          fone_pessoa.ddd as ddd,
          fone_pessoa.fone as fone,
          pessoa_responsavel.nome as nome_responsavel,
          educacenso_cod_escola.cod_escola_inep as inep,
          escola.ativo
         from pmieducar.escola
         inner join cadastro.juridica on(escola.ref_idpes = juridica.idpes)
         left join cadastro.pessoa on(juridica.idpes = pessoa.idpes)
         left join cadastro.pessoa pessoa_responsavel on(escola.ref_idpes_gestor = pessoa_responsavel.idpes)
         left join person_has_place ON person_has_place.person_id = escola.ref_idpes and person_has_place.type = 1
         left join addresses ON TRUE AND addresses.id = person_has_place.place_id
         left join cadastro.fone_pessoa on(fone_pessoa.idpes = pessoa.idpes and fone_pessoa.tipo = 1)
         left join modules.educacenso_cod_escola on (educacenso_cod_escola.cod_escola = escola.cod_escola)';

        $escolas = $this->fetchPreparedQuery($sql);

        if (empty($escolas)) {
            $this->messenger->append('Desculpe, mas não existem escolas cadastradas');

            return ['escolas' => 0];
        } else {
            foreach ($escolas as &$escola) {
                $escola['nome'] = Portabilis_String_Utils::toUtf8($escola['nome']);
                $escola['complemento'] = Portabilis_String_Utils::toUtf8($escola['complemento']);
                $escola['logradouro'] = Portabilis_String_Utils::toUtf8($escola['logradouro']);
                $escola['bairro'] = Portabilis_String_Utils::toUtf8($escola['bairro']);
                $escola['municipio'] = Portabilis_String_Utils::toUtf8($escola['municipio']);
                $escola['nome_responsavel'] = Portabilis_String_Utils::toUtf8($escola['nome_responsavel']);
            }

            $attrs = [
                'cod_escola',
                'nome',
                'cep',
                'numero',
                'complemento',
                'logradouro',
                'bairro',
                'municipio',
                'uf',
                'pais',
                'email',
                'ddd',
                'fone',
                'nome_responsavel',
                'inep',
                'ativo',
            ];

            return ['escolas' => Portabilis_Array_Utils::filterSet($escolas, $attrs)];
        }
    }

    protected function getEscolasMultipleSearch()
    {
        $cod_usuario = \Illuminate\Support\Facades\Auth::id();
        $permissao = new clsPermissoes;
        $nivel = $permissao->nivel_acesso($cod_usuario);
        $cursoId = $this->getRequest()->curso_id;

        $sql = 'SELECT cod_escola as id,
                 COALESCE(juridica.fantasia, nm_escola) as nome
            from pmieducar.escola
            left join cadastro.pessoa on(escola.ref_idpes = pessoa.idpes)
            left join cadastro.juridica on(juridica.idpes = pessoa.idpes)
            left join pmieducar.escola_complemento ON (escola_complemento.ref_cod_escola = escola.cod_escola)
           inner join pmieducar.escola_curso on(escola.cod_escola = escola_curso.ref_cod_escola)
           inner join pmieducar.curso on(escola_curso.ref_cod_curso = curso.cod_curso)
          where escola.ativo = 1
            and curso.ativo = 1
            and escola_curso.ativo = 1';

        if (is_numeric($cod_usuario) && $nivel == App_Model_NivelTipoUsuario::ESCOLA) {
            $escolas = $this->getEscolasUsuarios($cod_usuario);
            if (!empty($escolas['escolas'])) {
                $escolas = implode(', ', $escolas['escolas']);
                $sql .= " and escola.cod_escola in ({$escolas})";
            }
        }

        if (is_numeric($cursoId)) {
            $sql .= ' and curso.cod_curso = $1';
            $sql = $this->fetchPreparedQuery($sql, [$cursoId]);
        } else {
            $sql = $this->fetchPreparedQuery($sql);
        }

        $escolas = Portabilis_Array_Utils::setAsIdValue($sql, 'id', 'nome');

        return ['options' => $escolas];
    }

    protected function getEscolaAnoLetivo()
    {
        $escolaId = $this->getRequest()->escola_id;

        if (is_numeric($escolaId)) {
            $sql = 'SELECT ano FROM pmieducar.escola_ano_letivo WHERE ref_cod_escola = $1 AND andamento = 1 AND ativo = 1;';
            $ano = $this->fetchPreparedQuery($sql, [$escolaId]);
        }

        return $ano[0];
    }

    protected function getDependenciaAdministrativaEscola()
    {
        $escolaId = $this->getRequest()->escola_id;

        if (is_numeric($escolaId)) {
            $sql = 'SELECT dependencia_administrativa
              FROM pmieducar.escola
             WHERE cod_escola = $1;';
            $dependenciaAdministrativa = $this->fetchPreparedQuery($sql, [$escolaId], false, 'first-line');
        }

        return $dependenciaAdministrativa;
    }

    protected function getEscolasUsuarios($ref_cod_usuario = null)
    {
        if (!$ref_cod_usuario) {
            $ref_cod_usuario = $this->getRequest()->id;
        }

        if (!$ref_cod_usuario) {
            return null;
        }

        $escolasUsuario = new clsPmieducarEscolaUsuario;
        $escolasUsuario = $escolasUsuario->lista($ref_cod_usuario);

        $escolas = [];

        foreach ($escolasUsuario as $escola) {
            $escolas[] = $escola['ref_cod_escola'];
        }

        return ['escolas' => $escolas];
    }

    protected function getEscolasSelecao()
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $permissao = new clsPermissoes;
        $nivel = $permissao->nivel_acesso($userId);

        if (
            $nivel == App_Model_NivelTipoUsuario::ESCOLA ||
            $nivel == App_Model_NivelTipoUsuario::BIBLIOTECA
        ) {
            $escolas_usuario = [];
            $escolasUser = App_Model_IedFinder::getEscolasUser($userId);

            foreach ($escolasUser as $e) {
                $escolas_usuario['__'.$e['ref_cod_escola']] = mb_strtoupper($e['nome']);
            }

            return ['options' => $escolas_usuario];
        }

        $instituicao = $this->getRequest()->instituicao;
        $escolasInstituicao = App_Model_IedFinder::getEscolas($instituicao);

        foreach ($escolasInstituicao as $id => $nome) {
            $escolas['__'.$id] = mb_strtoupper($this->toUtf8($nome));
        }

        return ['options' => $escolas];
    }

    protected function getEscolasSelecaoSemFiltroPorUsuario()
    {
        $instituicao = $this->getRequest()->instituicao;
        $escolasInstituicao = App_Model_IedFinder::getEscolas($instituicao);

        foreach ($escolasInstituicao as $id => $nome) {
            $escolas['__'.$id] = mb_strtoupper($this->toUtf8($nome));
        }

        return ['options' => $escolas];
    }

    /**
     * Retorna os parâmetros das escolas.
     *
     *  - cod_escola
     *  - utiliza_regra_diferenciada
     *  - updated_at
     *
     * A query string "modified" pode ser informada para limitar os registros
     * por data.
     *
     * @return array
     */
    protected function getParametrosEscolas()
    {
        $modified = request('modified');

        $schools = LegacySchool::query()
            ->select(['cod_escola', 'utiliza_regra_diferenciada', 'updated_at'])
            ->when($modified, function ($query) use ($modified) {
                return $query->where('updated_at', '>=', $modified);
            })
            ->get();

        return [
            'escolas' => $schools,
        ];
    }

    protected function canGetSchoolAddress()
    {
        return $this->validatesPresenceOf('escola_id');
    }

    protected function getSchoolAddress()
    {
        if ($this->canGetSchoolAddress()) {
            $escola_id = $this->getRequest()->escola_id;

            $sql = '
            SELECT a.country, a.state, fcn_upper(a.city) as city, a.country_id, a.state_abbreviation FROM pmieducar.escola e
                LEFT JOIN person_has_place php ON php.person_id = e.ref_idpes
                LEFT JOIN addresses a ON a.id = php.place_id
            WHERE e.cod_escola = $1
            ';

            return $this->fetchPreparedQuery($sql, [$escola_id], false, 'first-line');
        }

        return false;
    }

    protected function getEscolaSerieDisciplinasAnosLetivos()
    {
        $escolaId = $this->getRequest()->escola ?? 0;
        $modified = $this->getRequest()->modified;
        $whereModified = '';

        if ($modified) {
            $whereModified = " WHERE updated_at >= '$modified' ";
        }

        $sql = "
            SELECT
                escola_id,
                json_agg(
                    json_build_object(
                        'serie_id', serie_id,
                        'disciplinas_anos_letivos', disciplinas_anos_letivos
                    )
                ) AS series_disciplinas_anos_letivos,
                json_agg(
                    json_build_object(
                        'serie_id', serie_id,
                        'disciplinas_etapas_utilizadas', disciplinas_etapas_utilizadas
                        )
                ) AS series_disciplinas_etapas_utilizadas
            FROM (
                SELECT
                    ref_ref_cod_escola AS escola_id,
                    ref_ref_cod_serie AS serie_id,
                    json_agg(
                        json_build_object(
                            ref_cod_disciplina, anos_letivos
                        )
                    ) AS disciplinas_anos_letivos,
                    json_agg(
                        json_build_object(
                            ref_cod_disciplina,  etapas_utilizadas
                            )
                    ) AS disciplinas_etapas_utilizadas
                FROM pmieducar.escola_serie_disciplina
                $whereModified
                GROUP BY
                ref_ref_cod_escola,
                ref_ref_cod_serie
            ) esd
            WHERE escola_id in ($escolaId)
            GROUP BY escola_id;
        ";

        $escolas = $this->fetchPreparedQuery($sql);
        $attrs = ['escola_id', 'series_disciplinas_anos_letivos', 'series_disciplinas_etapas_utilizadas'];
        $escolas = Portabilis_Array_Utils::filterSet($escolas, $attrs);

        foreach ($escolas as $key => $escola) {
            $escolas[$key]['series_disciplinas_anos_letivos'] = json_decode($escola['series_disciplinas_anos_letivos']);
            $escolas[$key]['series_disciplinas_etapas_utilizadas'] = json_decode($escola['series_disciplinas_etapas_utilizadas']);
        }

        return ['escolas' => $escolas];
    }

    public function Gerar()
    {
        if ($this->isRequestFor('get', 'escola')) {
            $this->appendResponse($this->get());
        } elseif ($this->isRequestFor('get', 'escola-search')) {
            $this->appendResponse($this->search());
        } elseif ($this->isRequestFor('put', 'escola')) {
            $this->appendResponse($this->put());
        } elseif ($this->isRequestFor('get', 'etapas-por-escola')) {
            $this->appendResponse($this->getEtapasPorEscola());
        } elseif ($this->isRequestFor('get', 'etapas-da-escola-por-ano')) {
            $this->appendResponse($this->getEtapasDaEscolaPorAno());
        } elseif ($this->isRequestFor('get', 'info-escolas')) {
            $this->appendResponse($this->getInformacaoEscolas());
        } elseif ($this->isRequestFor('get', 'escolas-multiple-search')) {
            $this->appendResponse($this->getEscolasMultipleSearch());
        } elseif ($this->isRequestFor('get', 'escola-ano-letivo')) {
            $this->appendResponse($this->getEscolaAnoLetivo());
        } elseif ($this->isRequestFor('get', 'escola-dependencia-administrativa')) {
            $this->appendResponse($this->getDependenciaAdministrativaEscola());
        } elseif ($this->isRequestFor('get', 'escolas-usuario')) {
            $this->appendResponse($this->getEscolasUsuarios());
        } elseif ($this->isRequestFor('get', 'escolas-para-selecao')) {
            $this->appendResponse($this->getEscolasSelecao());
        } elseif ($this->isRequestFor('get', 'escolas-para-selecao-sem-filtro-por-usuario')) {
            $this->appendResponse($this->getEscolasSelecaoSemFiltroPorUsuario());
        } elseif ($this->isRequestFor('get', 'parametros-escolas')) {
            $this->appendResponse($this->getParametrosEscolas());
        } elseif ($this->isRequestFor('get', 'endereco-escola')) {
            $this->appendResponse($this->getSchoolAddress());
        } elseif ($this->isRequestFor('get', 'escola-serie-disciplinas-anos-letivos')) {
            $this->appendResponse($this->getEscolaSerieDisciplinasAnosLetivos());
        } else {
            $this->notImplementedOperationError();
        }
    }
}
