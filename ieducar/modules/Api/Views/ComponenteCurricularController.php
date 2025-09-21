<?php

use App\Models\LegacyInstitution;
use App\Models\MigratedDiscipline;
use Illuminate\Support\Facades\DB;

class ComponenteCurricularController extends ApiCoreController
{
    // search options

    protected function searchOptions()
    {
        return ['namespace' => 'modules', 'idAttr' => 'id'];
    }

    // subescreve para pesquisar %query%, e nao query% como por padrão
    protected function sqlsForStringSearch()
    {
        return 'select distinct id, nome as name from modules.componente_curricular
            where lower((nome)) like \'%\'||lower(($1))||\'%\' order by nome limit 15';
    }

    // subscreve formatResourceValue para não adicionar 'id -' a frente do resultado
    protected function formatResourceValue($resource)
    {
        return mb_strtoupper($resource['name'], 'UTF-8');
    }

    public function getComponentesCurricularesSearch()
    {
        $sql = 'SELECT componente_curricular_id FROM modules.professor_turma_disciplina WHERE professor_turma_id = $1';

        $array = [];

        $resources = Portabilis_Utils_Database::fetchPreparedQuery($sql, ['params' => [$this->getRequest()->id]]);

        foreach ($resources as $reg) {
            $array[] = $reg['componente_curricular_id'];
        }

        return ['componentecurricular' => $array];
    }

    public function canGetComponentesCurriculares()
    {
        return $this->validatesPresenceOf('instituicao_id');
    }

    public function canGetComponentesCurricularesPorEscolaSerieAno()
    {
        return $this->validatesPresenceOf('escola_id') &&
            $this->validatesPresenceOf('serie_id') &&
            $this->validatesPresenceOf('ano');
    }

    public function getComponentesCurriculares()
    {
        if ($this->canGetComponentesCurriculares()) {
            $instituicaoId = $this->getRequest()->instituicao_id;
            $areaConhecimentoId = $this->getRequest()->area_conhecimento_id;
            $modified = $this->getRequest()->modified;

            $where = '';
            $wheres = [];
            $params = [$instituicaoId];

            if ($areaConhecimentoId) {
                $wheres[] = 'area_conhecimento_id = '. $areaConhecimentoId;
            }

            if ($modified) {
                $params[] = $modified;
                $wheres[] = 'componente_curricular.updated_at >= $2';
            }

            if (count($wheres)) {
                $where = ' AND ' . implode(' AND ', $wheres);
            }

            $sql = 'SELECT componente_curricular.id, componente_curricular.nome, area_conhecimento_id, area_conhecimento.nome AS nome_area, ordenamento, componente_curricular.updated_at
                FROM modules.componente_curricular
               INNER JOIN modules.area_conhecimento ON (area_conhecimento.id = componente_curricular.area_conhecimento_id)
                WHERE componente_curricular.instituicao_id = $1
                ' . $where . '
                ORDER BY componente_curricular.updated_at, componente_curricular.nome ';
            $disciplinas = $this->fetchPreparedQuery($sql, $params);

            $attrs = ['id', 'nome', 'area_conhecimento_id', 'nome_area', 'ordenamento', 'updated_at'];
            $disciplinas = Portabilis_Array_Utils::filterSet($disciplinas, $attrs);

            return ['disciplinas' => $disciplinas];
        }
    }

    private function getComponentesCurricularesMigrados()
    {
        $modified = $this->getRequest()->modified;

        $query = MigratedDiscipline::query();

        if ($modified) {
            $query->where('created_at', '>=', $modified);
        }

        return ['disciplinas' => $query->get()];
    }

    public function getComponentesCurricularesPorSerie()
    {
        if ($this->canGetComponentesCurriculares()) {
            $instituicaoId = (int) $this->getRequest()->instituicao_id;
            $serieId = (int) $this->getRequest()->serie_id;
            $areaDeConhecimento = (int) $this->getRequest()->area_conhecimento;

            $sql = '
                SELECT componente_curricular.id,
                       componente_curricular.nome,
                       carga_horaria,
                       tipo_nota,
                       array_to_json(componente_curricular_ano_escolar.anos_letivos) anos_letivos,
                       area_conhecimento_id,
                       area_conhecimento.nome AS nome_area,
                       componente_curricular_ano_escolar.hora_falta,
                       exists (SELECT * FROM modules.componente_curricular_turma cct
                                    INNER JOIN modules.componente_curricular cc ON cc.id = cct.componente_curricular_id
                                    WHERE TRUE
                                        AND cct.componente_curricular_id = componente_curricular.id
                                        AND cct.ano_escolar_id =  $3
                        ) contem_componente_curricular_turma,
                        exists (
                            select * from pmieducar.matricula m
                            join modules.nota_aluno na on na.matricula_id = m.cod_matricula
                            join modules.nota_componente_curricular ncc on ncc.nota_aluno_id = na.id
                            where m.ref_ref_cod_serie = $3
                            and ncc.componente_curricular_id = componente_curricular.id
                        ) contem_notas,

                        exists (
                            select * from pmieducar.matricula m
                            join modules.falta_aluno fa on fa.matricula_id  = m.cod_matricula
                            join modules.falta_componente_curricular fcc on fcc.falta_aluno_id  = fa.id
                            where m.ref_ref_cod_serie = $3
                            and fcc.componente_curricular_id = componente_curricular.id
                        ) contem_faltas,
                         exists (
                            select * from modules.parecer_componente_curricular pcc
                            where pcc.componente_curricular_id = componente_curricular.id
                        ) contem_paracer
                FROM modules.componente_curricular
                    INNER JOIN modules.componente_curricular_ano_escolar ON (componente_curricular_ano_escolar.componente_curricular_id = componente_curricular.id)
                    INNER JOIN modules.area_conhecimento ON (area_conhecimento.id = componente_curricular.area_conhecimento_id)
                WHERE componente_curricular.instituicao_id = $1
                  AND ano_escolar_id = $3
                  AND modules.area_conhecimento.id = $2
                ORDER BY nome ';
            $disciplinas = $this->fetchPreparedQuery($sql, [$instituicaoId, $areaDeConhecimento, $serieId]);

            $attrs = ['id', 'nome', 'anos_letivos', 'carga_horaria', 'tipo_nota', 'area_conhecimento_id', 'nome_area', 'hora_falta', 'contem_componente_curricular_turma', 'contem_notas', 'contem_faltas', 'contem_paracer'];
            $disciplinas = Portabilis_Array_Utils::filterSet($disciplinas, $attrs);

            foreach ($disciplinas as &$disciplina) {
                $disciplina['anos_letivos'] = json_decode($disciplina['anos_letivos']);
                $disciplina['hora_falta'] = $disciplina['hora_falta'] ? (float) $disciplina['hora_falta'] : null;
                $disciplina['carga_horaria'] = (float) $disciplina['carga_horaria'];
            }

            return ['disciplinas' => $disciplinas];
        }
    }

    public function getComponentesCurricularesPorEscolaSerieAno()
    {
        if ($this->canGetComponentesCurricularesPorEscolaSerieAno()) {
            $escolaId = $this->getRequest()->escola_id;
            $serieId = $this->getRequest()->serie_id;
            $ano = $this->getRequest()->ano;
            $componentes = App_Model_IedFinder::getEscolaSerieDisciplina($serieId, $escolaId, null, null, null, true, $ano);
            $componente_curricular_turma = LegacyInstitution::whereHas('schools', fn ($q) => $q->where('cod_escola', $escolaId))->value('componente_curricular_turma');
            $componentesCurriculares = array_map(function ($componente) use ($componente_curricular_turma) {
                return [
                    'id' => $componente->id,
                    'nome' => $componente->nome,
                    'carga_horaria' => $componente->cargaHoraria,
                    'abreviatura' => $componente->abreviatura,
                    'permite_por_turma' => $componente_curricular_turma,
                ];
            }, array_values($componentes));

            return ['componentes_curriculares' => $componentesCurriculares];
        }
    }

    protected function getComponentesCurricularesForMultipleSearch()
    {
        if ($this->canGetComponentesCurriculares()) {
            $serieId = $this->getRequest()->serie_id;
            $turmaId = $this->getRequest()->turma_id;
            $ano = $this->getRequest()->ano;
            $areaConhecimentoId = $this->getRequest()->area_conhecimento_id;
            $allDisciplinesMulti = dbBool($this->getRequest()->allDisciplinesMulti);

            $componentes = [];

            if ($allDisciplinesMulti) {
                $componentes = $this->getComponentesTurmaMulti($turmaId, $areaConhecimentoId);
            }

            if (count($componentes) > 0) {
                return ['options' => $componentes];
            }

            $sql = 'SELECT cc.id,
                       cc.nome
                  FROM pmieducar.turma
                 INNER JOIN modules.componente_curricular_turma cct ON (cct.turma_id = turma.cod_turma
                                                                    AND cct.escola_id = turma.ref_ref_cod_escola)
                 INNER JOIN modules.componente_curricular cc ON (cc.id = cct.componente_curricular_id)
                 INNER JOIN modules.area_conhecimento ac ON (ac.id = cc.area_conhecimento_id)
                 INNER JOIN pmieducar.escola_ano_letivo al ON (al.ref_cod_escola = turma.ref_ref_cod_escola)
                 WHERE turma.cod_turma = $1
                   AND cc.id <> COALESCE(turma.ref_cod_disciplina_dispensada,0)
                   AND al.ano = $2 ';
            $params = [$turmaId, $ano];

            if ($areaConhecimentoId) {
                $sql .= " AND area_conhecimento_id IN ({$areaConhecimentoId}) ";
            }

            $sql .= ' ORDER BY ac.secao,
                          ac.nome,
                          cc.ordenamento,
                          cc.nome ';

            $componentesCurriculares = $this->fetchPreparedQuery($sql, $params);

            if (count($componentesCurriculares) < 1) {
                $sql = 'SELECT cc.id,
                       cc.nome
                  FROM pmieducar.turma AS t
                INNER JOIN pmieducar.escola_serie_disciplina esd ON (esd.ref_ref_cod_escola = t.ref_ref_cod_escola
                                                                 AND esd.ref_ref_cod_serie = coalesce($3, t.ref_ref_cod_serie)
                                                                 AND esd.ativo = 1)
                INNER JOIN modules.componente_curricular cc ON (cc.id = esd.ref_cod_disciplina)
                INNER JOIN modules.area_conhecimento ac ON (ac.id = cc.area_conhecimento_id)
                INNER JOIN pmieducar.escola_ano_letivo al ON (al.ref_cod_escola = esd.ref_ref_cod_escola
                                                          AND al.ativo = 1)
                WHERE t.cod_turma = $1
                  AND al.ano = $2
                  AND $2 = ANY(esd.anos_letivos)
                  AND t.ativo = 1
                  AND cc.id <> COALESCE(t.ref_cod_disciplina_dispensada,0)
                  ';

                $params = [$turmaId, $ano, $serieId];

                if ($areaConhecimentoId) {
                    $sql .= " AND area_conhecimento_id IN ({$areaConhecimentoId}) ";
                }
                $sql .= ' ORDER BY ac.secao,
                         ac.nome,
                         cc.ordenamento,
                         cc.nome ';

                $componentesCurriculares = $this->fetchPreparedQuery($sql, $params);
            }

            $componentesCurriculares = Portabilis_Array_Utils::setAsIdValue($componentesCurriculares, 'id', 'nome');

            return ['options' => $componentesCurriculares];
        }
    }

    private function getComponentesTurmaMulti($turmaId, $areaConhecimentoId)
    {
        $areaConhecimentoId = explode(',', $areaConhecimentoId);
        $query = DB::table('pmieducar.turma as t')
            ->select('cc.id', 'cc.nome')
            ->join('pmieducar.turma_serie as ts', 'ts.turma_id', '=', 't.cod_turma')
            ->join('pmieducar.escola_serie as es', function ($join) {
                $join->on('es.ref_cod_serie', '=', 'ts.serie_id');
                $join->on('es.ref_cod_escola', '=', 't.ref_ref_cod_escola');
            })
            ->join('pmieducar.escola_serie_disciplina as esd', function ($join) {
                $join->on('esd.ref_ref_cod_serie', '=', 'es.ref_cod_serie');
                $join->on('esd.ref_ref_cod_escola', '=', 'es.ref_cod_escola');
            })
            ->join('modules.componente_curricular as cc', 'cc.id', '=', 'esd.ref_cod_disciplina')
            ->where('t.cod_turma', $turmaId)
            ->whereRaw('t.ano = ANY(esd.anos_letivos)')
            ->where('t.multiseriada', 1);

        if (count(array_filter($areaConhecimentoId)) > 0) {
            $query->whereIn('cc.area_conhecimento_id', $areaConhecimentoId);
        }

        return $query->distinct()
            ->get()
            ->pluck('nome', 'id')
            ->toArray();
    }

    public function Gerar()
    {
        if ($this->isRequestFor('get', 'componente_curricular-search')) {
            $this->appendResponse($this->search());
        } elseif ($this->isRequestFor('get', 'componentecurricular-search')) {
            $this->appendResponse($this->getComponentesCurricularesSearch());
        } elseif ($this->isRequestFor('get', 'componentes-curriculares')) {
            $this->appendResponse($this->getComponentesCurriculares());
        } elseif ($this->isRequestFor('get', 'componentes-curriculares-serie')) {
            $this->appendResponse($this->getComponentesCurricularesPorSerie());
        } elseif ($this->isRequestFor('get', 'componentes-curriculares-escola-serie-ano')) {
            $this->appendResponse($this->getComponentesCurricularesPorEscolaSerieAno());
        } elseif ($this->isRequestFor('get', 'componentes-curriculares-for-multiple-search')) {
            $this->appendResponse($this->getComponentesCurricularesForMultipleSearch());
        } elseif ($this->isRequestFor('get', 'componentes-curriculares-migrados')) {
            $this->appendResponse($this->getComponentesCurricularesMigrados());
        } else {
            $this->notImplementedOperationError();
        }
    }
}
