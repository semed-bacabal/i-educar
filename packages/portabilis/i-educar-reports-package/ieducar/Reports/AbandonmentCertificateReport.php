<?php

use iEducar\Reports\JsonDataSource;

class AbandonmentCertificateReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @var string
     */
    private $modelo;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'abandonment-certificate';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;
        $ano = $this->args['ano'] ?: 0;

        return "
SELECT public.fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
       public.fcn_upper(instituicao.nm_responsavel) AS nm_responsavel,
       coalesce(instituicao.altera_atestado_para_declaracao, false) AS altera_atestado_para_declaracao,
       escola.cod_escola as cod_escola,
       escola_ano_letivo.ano,
       (SELECT public.fcn_upper(curso.nm_curso)
        FROM pmieducar.curso
    WHERE  curso.cod_curso = matricula.ref_cod_curso AND
                       curso.ativo = 1) as nm_curso,
       (SELECT serie.nm_serie
        FROM pmieducar.serie
     WHERE serie.cod_serie = matricula.ref_ref_cod_serie AND
                       serie.ativo = 1) as nm_serie,
  coalesce((SELECT turma.nm_turma
     FROM pmieducar.matricula_turma,
          pmieducar.turma
    WHERE matricula_turma.ref_cod_turma = turma.cod_turma
      and matricula_turma.ref_cod_matricula = {$matricula}
    ORDER BY sequencial
    LIMIT 1), 'não informada') as nm_turma,
  coalesce((SELECT turma_turno.nome
     FROM pmieducar.matricula_turma,
          pmieducar.turma,
          pmieducar.turma_turno
    WHERE matricula_turma.ref_cod_turma = turma.cod_turma
      and turma.turma_turno_id = turma_turno.id
      and matricula_turma.ref_cod_matricula = {$matricula}
    ORDER BY sequencial
    LIMIT 1), 'não informado') as periodo,
       aluno.cod_aluno as cod_aluno,
       matricula.cod_matricula as cod_matricula,
       public.fcn_upper(pessoa.nome) as nome,
       public.fcn_upper(instituicao.cidade) as cidade,
       to_char(CURRENT_DATE,'dd/mm/yyyy') as data_atual,
     to_char(fisica.data_nasc,'dd/mm/yyyy') as data_nasc,
      COALESCE((SELECT municipio.nome || ' - ' || sigla_uf
       FROM public.municipio
        WHERE municipio.idmun = fisica.idmun_nascimento),'Não informado') as municipio_uf_nascimento,
  fcn_upper(COALESCE((select pessoa_pai.nome from cadastro.pessoa as pessoa_pai where
  pessoa_pai.idpes = fisica.idpes_pai), aluno.nm_pai, '')) as nm_pai,
  fcn_upper(COALESCE((select pessoa_mae.nome from cadastro.pessoa as pessoa_mae where
  pessoa_mae.idpes = fisica.idpes_mae), aluno.nm_mae, '')) as nm_mae,
      (SELECT COALESCE((SELECT COALESCE (public.fcn_upper(ps.nome),public.fcn_upper(juridica.fantasia))
          FROM cadastro.pessoa ps,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
         ps.idpes = escola.ref_idpes),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS nm_escola,
   (SELECT cod_aluno_inep
      FROM modules.educacenso_cod_aluno
     WHERE educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) as cod_inep,
   (SELECT MAX(f.nis_pis_pasep)
      FROM pmieducar.matricula m,
           pmieducar.aluno a,
           cadastro.fisica f
     WHERE matricula.cod_matricula = m.cod_matricula
       AND m.ref_cod_aluno = a.cod_aluno
       AND a.ref_idpes = f.idpes) AS cod_nis,
        aluno.aluno_estado_id as aluno_estado_id,
        trunc(modules.frequencia_da_matricula(cod_matricula)::numeric,2) as frequencia,
        (SELECT fcn_upper(p.nome) FROM cadastro.pessoa p WHERE escola.ref_idpes_gestor = p.idpes) as diretor,
(select fcn_upper(p.nome)
         from cadastro.pessoa p
         inner join pmieducar.escola e on (p.idpes = e.ref_idpes_secretario_escolar)
         where e.cod_escola = {$escola}) as secretario,
coalesce(to_char(matricula.data_cancel, 'dd/mm/yyyy'),  'não informado') as data_abandono,
coalesce((select at.nome
           from pmieducar.matricula m,
                pmieducar.abandono_tipo at
          where matricula.cod_matricula = m.cod_matricula
            and m.ref_cod_abandono_tipo = at.cod_abandono_tipo), 'não informado') as motivo_abandono
  FROM pmieducar.aluno,
       pmieducar.matricula,
       cadastro.fisica,
       cadastro.pessoa,
       pmieducar.instituicao,
       pmieducar.escola,
       pmieducar.escola_ano_letivo
 WHERE escola_ano_letivo.ano = {$ano} AND
       instituicao.cod_instituicao = {$instituicao} AND
       escola.cod_escola =
       CASE
          WHEN
            {$escola} > 0
          THEN
            {$escola}
          ELSE
            (select ref_ref_cod_escola from pmieducar.matricula where cod_matricula = {$matricula})
       END AND
       matricula.cod_matricula = (SELECT MAX(cod_matricula)
                            from pmieducar.matricula mt,
                                         pmieducar.aluno al
          where mt.ref_cod_aluno = matricula.ref_cod_aluno AND
        matricula.cod_matricula = {$matricula}  AND
        mt.ano = matricula.ano AND
        mt.ref_ref_cod_escola = matricula.ref_ref_cod_escola AND
        mt.ref_ref_cod_serie = matricula.ref_ref_cod_serie AND
        mt.ativo  = 1) AND
       pessoa.idpes = fisica.idpes AND
       fisica.idpes = aluno.ref_idpes AND
       aluno.cod_aluno = matricula.ref_cod_aluno AND
       matricula.ref_cod_aluno = aluno.cod_aluno AND
       escola.ref_cod_instituicao = instituicao.cod_instituicao AND
       escola_ano_letivo.ref_cod_escola = escola.cod_escola AND
       matricula.ano = escola_ano_letivo.ano AND
       matricula.ativo  = 1 AND
       aluno.ativo = 1 AND
       escola.ativo = 1 AND
       instituicao.ativo = 1
        ";
    }
}
