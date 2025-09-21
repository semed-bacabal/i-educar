<?php

use App\Models\LegacyDeficiency;
use App\Models\LogUnification;
use iEducar\Modules\Educacenso\Validator\DeficiencyValidator;

class ServidorController extends ApiCoreController
{
    protected function searchOptions()
    {
        $escolaId = $this->getRequest()->escola_id ? $this->getRequest()->escola_id : 0;

        return ['sqlParams' => [$escolaId]];
    }

    protected function formatResourceValue($resource)
    {
        $nome = $this->toUtf8($resource['nome'], ['transform' => true]);

        return $nome;
    }

    protected function canGetServidoresDisciplinasTurmas()
    {
        return
            $this->validatesPresenceOf('ano') &&
            $this->validatesPresenceOf('instituicao_id') &&
            $this->validatesPresenceOf('escola');
    }

    protected function canGetServidores()
    {
        return $this->validatesPresenceOf('instituicao_id');
    }

    protected function sqlsForNumericSearch()
    {
        $sqls[] = 'SELECT p.idpes as id, p.nome
                FROM cadastro.pessoa p
                 LEFT JOIN cadastro.fisica f ON (p.idpes = f.idpes)
                 LEFT JOIN portal.funcionario fun ON (fun.ref_cod_pessoa_fj = f.idpes)
                INNER JOIN pmieducar.servidor s ON (s.cod_servidor = p.idpes)
                LEFT JOIN pmieducar.servidor_alocacao sa ON (s.cod_servidor = sa.ref_cod_servidor)

                WHERE p.idpes::varchar LIKE \'%\'||$1||\'%\'
                AND (CASE WHEN $2 = 0 THEN
                      1 = 1
                    ELSE
                      sa.ref_cod_escola = $2
                    END)
                LIMIT 15';

        return $sqls;
    }

    protected function sqlsForStringSearch()
    {
        $sqls[] = 'SELECT p.idpes as id, p.nome
                FROM cadastro.pessoa p
                 LEFT JOIN cadastro.fisica f ON (p.idpes = f.idpes)
                 LEFT JOIN portal.funcionario fun ON (fun.ref_cod_pessoa_fj = f.idpes)
                INNER JOIN pmieducar.servidor s ON (s.cod_servidor = p.idpes)
                LEFT JOIN pmieducar.servidor_alocacao sa ON (s.cod_servidor = sa.ref_cod_servidor)

                WHERE p.nome ILIKE \'%\'||$1||\'%\'
                AND (CASE WHEN $2 = 0 THEN
                      1 = 1
                    ELSE
                      sa.ref_cod_escola = $2
                    END)
                LIMIT 15';

        return $sqls;
    }

    protected function getServidores()
    {
        if ($this->canGetServidores() == false) {
            return;
        }

        $instituicaoId = $this->getRequest()->instituicao_id;
        $modified = $this->getRequest()->modified;

        $params = [$instituicaoId];

        $where = '';

        if ($modified) {
            $params[] = $modified;
            $where = ' AND greatest(p.data_rev::timestamp(0), s.updated_at) >= $2';
        }

        $sql = "
            SELECT
                s.cod_servidor as servidor_id,
                p.nome as nome,
                s.ativo as ativo,
                greatest(p.data_rev::timestamp(0), s.updated_at) as updated_at
            FROM pmieducar.servidor s
            INNER JOIN cadastro.pessoa p ON s.cod_servidor = p.idpes
            WHERE s.ref_cod_instituicao = $1
            {$where}
            order by updated_at
        ";

        $servidores = $this->fetchPreparedQuery($sql, $params);

        $attrs = ['servidor_id', 'nome', 'ativo', 'updated_at'];

        $servidores = Portabilis_Array_Utils::filterSet($servidores, $attrs);

        return ['servidores' => $servidores];
    }

    protected function getServidoresDisciplinasTurmas()
    {
        if ($this->canGetServidoresDisciplinasTurmas()) {
            $instituicaoId = $this->getRequest()->instituicao_id;
            $ano = $this->getRequest()->ano;
            $escola = $this->getRequest()->escola;
            $modified = $this->getRequest()->modified;

            $params = [$instituicaoId, $ano];

            if (is_array($escola)) {
                $escola = implode(', ', $escola);
            }

            $innerWhere = '';
            $whereDeleted = '';
            $having = '';

            if ($modified) {
                $params[] = $modified;
                $whereDeleted = 'AND pt.updated_at >= $3';
                // o filtro deve acontecer na query principal, fora da query das disciplinas para não limita-los e dar um falso positivo de exclusão.
                $having = 'HAVING MAX(tmp.updated_at) >= $3';
            }

            if ($ano) {
                $innerWhere .= " AND {$ano} = ANY(ccae.anos_letivos)";
            }

            $sql = "
                (
                    select
                        tmp.id,
                        tmp.servidor_id,
                        tmp.turma_id,
                        tmp.turno_id,
                        tmp.permite_lancar_faltas_componente,
                        string_agg(distinct concat(serie_id,'|',tmp.componente_curricular_id, '|', tmp.tipo_nota)::varchar, ',') as disciplinas,
                        string_agg(distinct concat( tmp.serie_id, ' ',tmp.componente_curricular_id)::varchar, ',') as disciplinas_serie,
                        max(tmp.updated_at) as updated_at,
                        max(tmp.deleted_at) as deleted_at
                    from (
                             select
                                 coalesce(ts.serie_id, t.ref_ref_cod_serie) as serie_id,
                                 pt.id,
                                 pt.servidor_id,
                                 pt.turma_id,
                                 pt.turno_id,
                                 pt.permite_lancar_faltas_componente,
                                 ptd.componente_curricular_id,
                                 ccae.tipo_nota,
                                 greatest(pt.updated_at, ccae.updated_at) as updated_at,
                                 CASE
                                     WHEN s.ativo = 0 THEN coalesce(s.data_exclusao::timestamp(0),s.updated_at::timestamp(0))
                                     WHEN t.ativo = 0 THEN t.updated_at::timestamp(0)
                                     ELSE NULL
                                 END AS deleted_at
                             from modules.professor_turma pt
                                      left join modules.professor_turma_disciplina ptd
                                                on ptd.professor_turma_id = pt.id
                                      inner join pmieducar.turma t
                                                 on t.cod_turma = pt.turma_id
                                      left join pmieducar.turma_serie ts on ts.turma_id = t.cod_turma
                                      inner join modules.componente_curricular_ano_escolar ccae
                                                on ccae.ano_escolar_id = coalesce(ts.serie_id, t.ref_ref_cod_serie)
                                                and ccae.componente_curricular_id = ptd.componente_curricular_id
                                      left join pmieducar.servidor s on s.cod_servidor = pt.servidor_id
                             where true
                             and pt.instituicao_id = $1
                             and pt.ano = $2
                             and t.ref_ref_cod_escola in ({$escola})
                            {$innerWhere}
                         ) as tmp
                    group by tmp.id, tmp.servidor_id, tmp.turma_id, tmp.turno_id, tmp.permite_lancar_faltas_componente
                    {$having}
                )
                union all
                (
                    select
                        pt.id,
                        pt.servidor_id,
                        pt.turma_id,
                        pt.turno_id,
                        null as permite_lancar_faltas_componente,
                        null as disciplinas,
                        null as disciplinas_serie,
                        pt.updated_at,
                        pt.deleted_at
                    from modules.professor_turma_excluidos pt
                    inner join pmieducar.turma t
                    on t.cod_turma = pt.turma_id
                    left join pmieducar.turma_serie ts on ts.turma_id = t.cod_turma
                    where true
                    and pt.instituicao_id = $1
                    and pt.ano = $2
                    and t.ref_ref_cod_escola in ({$escola})
                    {$whereDeleted}
                    group by pt.id,pt.servidor_id,pt.turma_id,pt.turno_id,pt.updated_at,pt.deleted_at
                )
                order by updated_at
            ";

            $vinculos = $this->fetchPreparedQuery($sql, $params);

            $attrs = ['id', 'servidor_id', 'turma_id', 'turno_id', 'permite_lancar_faltas_componente', 'disciplinas', 'disciplinas_serie', 'updated_at', 'deleted_at'];

            $vinculos = Portabilis_Array_Utils::filterSet($vinculos, $attrs);

            $vinculos = array_map(function ($vinculo) {
                if (is_null($vinculo['disciplinas_serie'])) {
                    $vinculo['disciplinas_serie'] = [];
                } elseif (is_string($vinculo['disciplinas_serie'])) {
                    $collect = collect(explode(',', $vinculo['disciplinas_serie']));
                    $collect = $collect->mapToGroups(function ($item, $key) {
                        [$key, $value] = explode(' ', $item);

                        return [$key => (int) $value];
                    });

                    $vinculo['disciplinas_serie'] = $collect;
                }

                if (is_null($vinculo['disciplinas'])) {
                    $vinculo['disciplinas'] = [];
                } elseif (is_string($vinculo['disciplinas'])) {
                    $vinculo['disciplinas'] = array_map(static function ($disciplina) {
                        [$serie_id, $disciplina_id, $tipo_nota] = explode('|', $disciplina);

                        return [
                            'id' => (int) $disciplina_id,
                            'serie_id' => (int) $serie_id,
                            'tipo_nota' => $tipo_nota == '' ? null : (int) $tipo_nota,
                        ];
                    }, explode(',', $vinculo['disciplinas']));
                }

                return $vinculo;
            }, $vinculos);

            return ['vinculos' => $vinculos];
        }
    }

    protected function getEscolaridade()
    {
        $idesco = $this->getRequest()->idesco;
        $sql = 'SELECT * FROM cadastro.escolaridade where idesco = $1 ';
        $escolaridade = $this->fetchPreparedQuery($sql, [$idesco], true, 'first-row');
        $escolaridade['descricao'] = Portabilis_String_Utils::toUtf8($escolaridade['descricao']);

        return ['escolaridade' => $escolaridade];
    }

    protected function getDadosServidor()
    {
        $servidor = $this->getRequest()->servidor_id;

        $sql = 'SELECT pessoa.nome,
                       pessoa.email,
                       educacenso_cod_docente.cod_docente_inep AS inep
                FROM pmieducar.servidor
                JOIN cadastro.pessoa ON pessoa.idpes = servidor.cod_servidor
                JOIN modules.educacenso_cod_docente ON educacenso_cod_docente.cod_servidor = servidor.cod_servidor
                WHERE servidor.cod_servidor = $1';

        $result = $this->fetchPreparedQuery($sql, [$servidor]);

        return ['result' => $result[0]];
    }

    protected function getUnificacoes()
    {
        $modified = $this->getRequest()->modified;

        $unificationsQuery = LogUnification::query();

        if ($modified) {
            $unificationsQuery->where('created_at', '>=', $modified);
        }

        $unificationsQuery->whereHas('personMain', function ($individualQuery) {
            $individualQuery->whereHas('employee');
        });

        $unificationsQuery->person();

        return ['unificacoes' => $unificationsQuery->get(['id', 'main_id', 'duplicates_id', 'created_at', 'active'])];
    }

    /**
     * @return bool
     */
    private function validateDeficiencies()
    {
        $deficiencias = explode(',', $this->getRequest()->deficiencias);
        $deficiencias = array_filter((array) $deficiencias);
        $deficiencias = $this->replaceByEducacensoDeficiencies($deficiencias);
        $validator = new DeficiencyValidator($deficiencias);

        if ($validator->isValid()) {
            return true;
        } else {
            $this->messenger->append($validator->getMessage());

            return false;
        }
    }

    /**
     * @return array
     */
    private function replaceByEducacensoDeficiencies($deficiencies)
    {
        $databaseDeficiencies = LegacyDeficiency::all()->getKeyValueArray('deficiencia_educacenso');
        $arrayEducacensoDeficiencies = [];

        foreach ($deficiencies as $deficiency) {
            $arrayEducacensoDeficiencies[] = $databaseDeficiencies[(int) $deficiency];
        }

        return $arrayEducacensoDeficiencies;
    }

    public function Gerar()
    {
        if ($this->isRequestFor('get', 'servidor-search')) {
            $this->appendResponse($this->search());
        } elseif ($this->isRequestFor('get', 'escolaridade')) {
            $this->appendResponse($this->getEscolaridade());
        } elseif ($this->isRequestFor('get', 'servidores-disciplinas-turmas')) {
            $this->appendResponse($this->getServidoresDisciplinasTurmas());
        } elseif ($this->isRequestFor('get', 'dados-servidor')) {
            $this->appendResponse($this->getDadosServidor());
        } elseif ($this->isRequestFor('get', 'verifica-deficiencias')) {
            $this->appendResponse($this->validateDeficiencies());
        } elseif ($this->isRequestFor('get', 'servidores')) {
            $this->appendResponse($this->getServidores());
        } elseif ($this->isRequestFor('get', 'unificacoes')) {
            $this->appendResponse($this->getUnificacoes());
        } else {
            $this->notImplementedOperationError();
        }
    }
}
