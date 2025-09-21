<?php

use iEducar\Reports\JsonDataSource;

class AuditEvaluationsFaultsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'audit-evaluations-faults';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('data_inicio');
        $this->addRequiredArg('data_fim');
    }


    public function getSqlMainReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $etapa = $this->args['etapa'] ?: 0;
        $aluno = $this->args['aluno'] ?: 0;
        $data_inicio = $this->args['data_inicio'];
        $data_fim = $this->args['data_fim'];

        return "SELECT pessoa.nome AS usuario,
       (case when view_auditoria.operacao = 1 then 'Inserção'
	     when view_auditoria.operacao = 2 then 'Edição'
	else 'Exclusão' end) AS operacao,
       to_char(view_auditoria.data_hora, 'dd/mm/yyyy HH24:MI:SS') AS data_hora,
       view_auditoria.instituicao AS instituicao,
       view_auditoria.escola AS escola,
       view_auditoria.curso AS curso,
       view_auditoria.serie AS serie,
       view_auditoria.turma AS turma,
       view_auditoria.aluno AS aluno,
       view_auditoria.componente_curricular AS componente_curricular,
       view_auditoria.etapa AS etapa,
       view_auditoria.nota_antiga AS nota_antiga,
       view_auditoria.nota_nova AS nota_nova
  FROM relatorio.view_auditoria
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = view_auditoria.usuario_id)
 WHERE view_auditoria.instituicao_id = {$instituicao}
   AND (CASE WHEN 0 = {$escola} THEN true ELSE view_auditoria.escola_id = {$escola} END)
   AND (CASE WHEN 0 = {$curso} THEN true ELSE view_auditoria.curso_id = {$curso} END)
   AND (CASE WHEN 0 = {$serie} THEN true ELSE view_auditoria.serie_id = {$serie} END)
   AND (CASE WHEN 0 = {$turma} THEN true ELSE view_auditoria.turma_id = {$turma} END)
   AND (CASE WHEN 0 = {$aluno} THEN true ELSE view_auditoria.aluno_id = {$aluno} END)
   AND (CASE WHEN 0 = {$etapa} THEN true ELSE view_auditoria.etapa = {$etapa}::varchar END)
   AND view_auditoria.data_hora::date between '{$data_inicio}'::date and '{$data_fim}'::date
 ORDER BY view_auditoria.data_hora";
    }
}
