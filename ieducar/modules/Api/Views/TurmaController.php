<?php

use App\Models\LegacyEvaluationRule;
use App\Models\LegacySchoolClass;
use App\Models\LegacySchoolClassGrade;

class TurmaController extends ApiCoreController
{
    // validators
    protected function validatesTurmaId()
    {
        return
            $this->validatesPresenceOf('id') &&
            $this->validatesExistenceOf('turma', $this->getRequest()->id);
    }

    protected function canGetTurmasPorEscola()
    {
        return
            $this->validatesPresenceOf('escola') &&
            $this->validatesPresenceOf('ano') &&
            $this->validatesPresenceOf('instituicao_id');
    }

    // validations
    protected function canGet()
    {
        return
            $this->canAcceptRequest() &&
            $this->validatesTurmaId();
    }

    protected function canGetAlunosExameTurma()
    {
        return
            $this->validatesPresenceOf('instituicao_id') &&
            $this->validatesPresenceOf('turma_id') &&
            $this->validatesPresenceOf('disciplina_id');
    }

    // api
    protected function get()
    {
        if (!$this->canGet()) {
            return void;
        }

        $id = $this->getRequest()->id;
        $turma = new clsPmieducarTurma;
        $turma->cod_turma = $id;
        $turma = $turma->detalhe();

        foreach ($turma as $k => $v) {
            if (is_numeric($k)) {
                unset($turma[$k]);
            }
        }

        return $turma;
    }

    protected function ordenaMatriculasPorDataBase()
    {
        $codTurma = $this->getRequest()->id;
        $parametros = [$codTurma];
        $sql = '
            SELECT
                cod_matricula,
                sequencial_fechamento,
                sequencial,
                relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS aluno,
                data_enturmacao,
                CASE WHEN dependencia THEN to_char(data_enturmacao,\'mmdd\')::int ELSE 0 END as ord_dependencia,
                CASE WHEN to_char(instituicao.data_base_remanejamento,\'mmdd\') is not null and to_char(data_enturmacao,\'mmdd\') > to_char(instituicao.data_base_remanejamento,\'mmdd\') THEN to_char(data_enturmacao,\'mmdd\')::int ELSE 0 END as data_aluno_order
            FROM pmieducar.matricula_turma
            INNER JOIN pmieducar.instituicao On (instituicao.ativo = 1)
            INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
            INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
            WHERE ref_cod_turma = $1
            AND matricula.ativo = 1
            AND (
                    CASE
                        WHEN matricula_turma.ativo = 1 THEN TRUE
                        WHEN matricula_turma.transferido THEN TRUE
                        WHEN matricula_turma.remanejado THEN TRUE
                        WHEN matricula.dependencia THEN TRUE
                        WHEN matricula_turma.abandono THEN TRUE
                        WHEN matricula_turma.reclassificado THEN TRUE
                        ELSE FALSE
                    END
                )
            ORDER BY ord_dependencia, data_aluno_order, aluno;
        ';

        $alunos = $this->fetchPreparedQuery($sql, $parametros);
        $attrs = [
            'cod_matricula',
            'sequencial_fechamento',
            'sequencial',
            'aluno',
            'data_enturmacao',
            'ord_dependencia',
            'data_fechamaneto',
        ];
        $alunos = Portabilis_Array_Utils::filterSet($alunos, $attrs);

        foreach ($alunos as $key => $aluno) {
            $parametros = [
                $codTurma,
                $aluno['cod_matricula'],
                $aluno['sequencial_fechamento'],
                $aluno['sequencial'],
                $key + 1,
            ];

            $sql = '
                UPDATE pmieducar.matricula_turma
                SET sequencial_fechamento = $5
                WHERE matricula_turma.ref_cod_turma = $1
                AND matricula_turma.ref_cod_matricula = $2
                AND sequencial_fechamento = $3
                AND sequencial = $4
            ';

            $this->fetchPreparedQuery($sql, $parametros);
        }
    }

    protected function ordenaAlunosDaTurmaAlfabetica()
    {
        $codTurma = $this->getRequest()->id;
        $objMatriculaTurma = new clsPmieducarMatriculaTurma;
        $lstMatriculaTurma = $objMatriculaTurma->lista(null, $codTurma, null, null, null, null, null, null, 3);

        $lstNomes = [];
        foreach ($lstMatriculaTurma as $matricula) {
            $lstNomes[] = [
                'nome' => limpa_acentos(mb_strtoupper($matricula['nome'])),
                'ref_cod_matricula' => $matricula['ref_cod_matricula'],
                'sequencial' => $matricula['sequencial'],
            ];
        }

        sort($lstNomes);
        array_unshift($lstNomes, 'indice zero');
        $quantidadeAlunos = count($lstNomes);

        for ($i = 1; $i < $quantidadeAlunos; $i++) {
            $sql = 'UPDATE pmieducar.matricula_turma
                SET sequencial_fechamento ='.$i.'
              WHERE matricula_turma.ref_cod_turma = '. $codTurma .'
                AND matricula_turma.ref_cod_matricula = '. $lstNomes[$i]['ref_cod_matricula'];

            $this->fetchPreparedQuery($sql);
        }
    }

    protected function ordenaSequencialAlunosTurma()
    {
        $this->ordenaAlunosDaTurmaAlfabetica();
    }

    protected function getTipoBoletim()
    {
        $turma = App_Model_IedFinder::getTurma($codTurma = $this->getRequest()->id);
        $serie = $this->getRequest()->serie_id;

        $tipo = $turma['tipo_boletim'];
        $tipoDiferenciado = $turma['tipo_boletim_diferenciado'];

        if ((int) $turma['multiseriada'] === 1) {
            $boletimPorSerie = LegacySchoolClassGrade::query()
                ->where('turma_id', $turma)
                ->where('serie_id', $serie)
                ->first();

            if ($boletimPorSerie instanceof LegacySchoolClassGrade) {
                $boletimPorSerie->toArray();
                $tipo = $boletimPorSerie['boletim_id'];
                $tipoDiferenciado = $boletimPorSerie['boletim_diferenciado_id'];
            }
        }

        $tipos = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();

        if ($tipoDiferenciado && $tipoDiferenciado != $tipo) {
            $this->appendResponse('tipo-boletim-diferenciado', $tipos[$tipoDiferenciado]);
        } else {
            $exists = LegacySchoolClass::query()->whereDifferentStudents($codTurma)->exists();

            if ($exists) {
                $this->appendResponse('tipo-boletim-diferenciado', $tipos[$tipoDiferenciado]);
            }
        }

        $pontos = LegacyEvaluationRule::query()
            ->whereHas('gradeYears', function ($q) use ($serie, $turma) {
                $q->where('serie_id', $serie);
                $q->where('ano_letivo', $turma['ano']);
            })
            ->value('pontos');

        $this->appendResponse('pontos', !empty($pontos));

        return ['tipo-boletim' => $tipos[$tipo]];
    }

    protected function getTurmasPorEscola()
    {
        if ($this->canGetTurmasPorEscola()) {
            $ano = $this->getRequest()->ano;
            $escola = $this->getRequest()->escola;
            $instituicaoId = $this->getRequest()->instituicao_id;
            $turnoId = $this->getRequest()->turno_id;
            $modified = $this->getRequest()->modified ?: null;

            $params = [$instituicaoId, $ano];

            if ($turnoId) {
                $turnoId = " AND t.turma_turno_id = {$turnoId} ";
            }

            if (is_array($escola)) {
                $escola = implode(',', $escola);
            }

            if ($modified) {
                $params[] = $modified;
                $modified = 'AND (t.updated_at >= $3 OR rasa.updated_at >= $3 OR ra.updated_at >= $3)';
            }

            $sql = "
                SELECT
                    t.cod_turma as id,
                    t.nm_turma as nome,
                    t.ano,
                    t.ref_ref_cod_escola as escola_id,
                    t.turma_turno_id as turno_id,
                    t.ref_cod_regente,
                    t.max_aluno,
                    json_agg(
                        json_build_object(
                            'serie_id', s.cod_serie,
                            'regra_avaliacao_id', ra.id
                        )
                    ) AS series_regras,
                    t.updated_at,
                    (
                        CASE t.ativo WHEN 1 THEN
                            NULL
                        ELSE
                            t.data_exclusao::timestamp(0)
                        END
                    ) AS deleted_at
                FROM pmieducar.turma t
                INNER JOIN pmieducar.escola e
                    ON e.cod_escola = t.ref_ref_cod_escola
                LEFT JOIN pmieducar.turma_serie ts ON ts.turma_id = t.cod_turma
                JOIN pmieducar.serie s ON s.cod_serie = coalesce(ts.serie_id, t.ref_ref_cod_serie)
                LEFT JOIN modules.regra_avaliacao_serie_ano rasa ON true
                    AND rasa.serie_id = s.cod_serie
                    AND rasa.ano_letivo = $2
                LEFT JOIN modules.regra_avaliacao ra
                    ON ra.id = (case when e.utiliza_regra_diferenciada and rasa.regra_avaliacao_diferenciada_id is not null then rasa.regra_avaliacao_diferenciada_id else rasa.regra_avaliacao_id end)
                WHERE t.ref_cod_instituicao = $1
                    AND t.ano = $2
                    AND t.ref_ref_cod_escola IN ({$escola})
                    {$turnoId}
                    {$modified}
                GROUP BY
                    t.cod_turma,
                    t.nm_turma,
                    t.ano,
                    t.ref_ref_cod_escola,
                    t.turma_turno_id
                ORDER BY t.updated_at, t.ref_ref_cod_escola, t.nm_turma
            ";

            $turmas = $this->fetchPreparedQuery($sql, $params);

            $attrs = ['id', 'nome', 'ano', 'escola_id', 'turno_id', 'curso_id', 'series_regras', 'ref_cod_regente', 'max_aluno', 'updated_at', 'deleted_at'];
            $turmas = Portabilis_Array_Utils::filterSet($turmas, $attrs);

            foreach ($turmas as $key => $turma) {
                $turmas[$key]['series_regras'] = json_decode($turma['series_regras']);
            }

            return ['turmas' => $turmas];
        }
    }

    protected function getAlunosExameTurma()
    {
        $instituicaoId = $this->getRequest()->instituicao_id;
        $turmaId = $this->getRequest()->turma_id;
        $disciplinaId = $this->getRequest()->disciplina_id;

        $sql = 'SELECT aluno.cod_aluno as id,
                   nota_exame.nota_exame as nota_exame
              from pmieducar.aluno
             inner join cadastro.pessoa on(aluno.ref_idpes = pessoa.idpes)
             inner join pmieducar.matricula on(aluno.cod_aluno = matricula.ref_cod_aluno)
             inner join pmieducar.matricula_turma on(matricula.cod_matricula = matricula_turma.ref_cod_matricula)
             inner join pmieducar.turma on(matricula_turma.ref_cod_turma = turma.cod_turma)
             inner join modules.nota_aluno on(matricula.cod_matricula = nota_aluno.matricula_id)
             inner join modules.nota_componente_curricular_media on(nota_aluno.id = nota_componente_curricular_media.nota_aluno_id)
             left join modules.nota_exame
                on matricula.cod_matricula = nota_exame.ref_cod_matricula
                and nota_componente_curricular_media.componente_curricular_id = nota_exame.ref_cod_componente_curricular
             where aluno.ativo = 1
               and matricula.ativo = 1
               and matricula_turma.ativo = 1
               and turma.ref_cod_instituicao = $1
               and matricula_turma.ref_cod_turma = $2
               and (case when $3 = 0 then true else $3 = nota_componente_curricular_media.componente_curricular_id end)
               and nota_componente_curricular_media.situacao = 7';

        $sql .= ' ORDER BY matricula_turma.sequencial_fechamento, translate(upper(pessoa.nome),\'áéíóúýàèìòùãõâêîôûäëïöüÿçÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ\',\'AEIOUYAEIOUAOAEIOUAEIOUYCAEIOUYAEIOUAOAEIOUAEIOUC\')';

        $params = [$instituicaoId, $turmaId, $disciplinaId];
        $alunos = $this->fetchPreparedQuery($sql, $params);
        $attrs = ['id', 'nota_exame'];
        $alunos = Portabilis_Array_Utils::filterSet($alunos, $attrs);

        return ['alunos' => $alunos];
    }

    protected function getSeriesDaTurma()
    {
        $schoolClass = $this->getRequest()->turma_id;
        $seriesDaTurma = LegacySchoolClassGrade::query()
            ->select('turma_serie.*', 'serie.ref_cod_curso', 'curso.padrao_ano_escolar')
            ->join('pmieducar.serie', 'serie.cod_serie', '=', 'turma_serie.serie_id')
            ->join('pmieducar.curso', 'curso.cod_curso', '=', 'serie.ref_cod_curso')
            ->where('turma_id', $schoolClass)
            ->get()
            ->toArray();

        return ['series_turma' => $seriesDaTurma];
    }

    public function Gerar()
    {
        if ($this->isRequestFor('get', 'turma')) {
            $this->appendResponse($this->get());
        } elseif ($this->isRequestFor('get', 'tipo-boletim')) {
            $this->appendResponse($this->getTipoBoletim());
        } elseif ($this->isRequestFor('get', 'ordena-matriculas-data-base')) {
            $this->appendResponse($this->ordenaMatriculasPorDataBase());
        } elseif ($this->isRequestFor('get', 'ordena-turma-alfabetica')) {
            $this->appendResponse($this->ordenaSequencialAlunosTurma());
        } elseif ($this->isRequestFor('get', 'turmas-por-escola')) {
            $this->appendResponse($this->getTurmasPorEscola());
        } elseif ($this->isRequestFor('get', 'alunos-exame-turma')) {
            $this->appendResponse($this->getAlunosExameTurma());
        } elseif ($this->isRequestFor('get', 'series-da-turma')) {
            $this->appendResponse($this->getSeriesDaTurma());
        } else {
            $this->notImplementedOperationError();
        }
    }
}
