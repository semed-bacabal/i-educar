<?php

use iEducar\Reports\JsonDataSource;

class ReportCardTransferenceReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public function templateName()
    {
        return 'report-card-transference';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
        $this->addRequiredArg('turma');
    }

    public function getSqlMainReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $transferido = $this->args['transferido'] ?: 0;
        $ano = $this->args['ano'] ?: 0;

        return "select nome_aluno.nome as aluno,
           matricula.cod_matricula,
           matricula.ano,
           instituicao.nm_instituicao,
           instituicao.nm_responsavel,
           relatorio.get_nome_escola(escola.cod_escola) as nome_escola,
           view_dados_escola.logradouro,
           view_dados_escola.email,
           view_dados_escola.telefone,
           curso.nm_curso,
           serie.nm_serie,
           turma.nm_turma,
           view_situacao.texto_situacao,
           regra_avaliacao.nota_maxima_geral as regra_avaliacao,
           replace(trunc(regra_avaliacao.media, 1)::varchar,'.',',') as regra_avaliacao_media,
                CASE WHEN regra_avaliacao.parecer_descritivo IN (2,5) THEN 1
                   WHEN regra_avaliacao.parecer_descritivo IN (3,6) THEN 2
                   ELSE 0
          END AS tipo_parecer,
(select count(1) from modules.parecer_geral where parecer_aluno_id = parecer_aluno.id) as parecer_geral,
(select count(1) from modules.parecer_componente_curricular where parecer_aluno_id = parecer_aluno.id) as parecer_componente,
(case when ((select ref_cod_escola_destino from pmieducar.transferencia_solicitacao where ref_cod_matricula_saida = matricula.cod_matricula and ativo = 1 order by data_transferencia limit 1) is null
             or (select ref_cod_escola_destino from pmieducar.transferencia_solicitacao where ref_cod_matricula_saida = matricula.cod_matricula and ativo = 1 order by data_transferencia limit 1) = 0)
      then
	   (select escola_destino_externa
	      from pmieducar.transferencia_solicitacao
	     where ref_cod_matricula_saida = matricula.cod_matricula
	       and transferencia_solicitacao.ativo = 1
	  order by data_transferencia limit 1)
      else
	   (select view_dados_escola.nome
	      from relatorio.view_dados_escola
	inner join pmieducar.transferencia_solicitacao on (view_dados_escola.cod_escola = transferencia_solicitacao.ref_cod_escola_destino)
	     where ref_cod_matricula_saida = matricula.cod_matricula
	       and transferencia_solicitacao.ativo = 1
	  order by data_transferencia limit 1)
      end) as nome_escola_destino,
           transferencia_tipo.nm_tipo as motivo,
           to_char(matricula.data_cancel, 'dd/MM/yyyy') as data_transferencia,
           transferencia_solicitacao.observacao,
           view_componente_curricular.nome as disciplinas,
          falta_aluno.tipo_falta,
           ncc1.nota_arredondada as nota_etapa_1,
	   (case when tipo_falta = 1
		 then
		     (select quantidade
		        from modules.falta_aluno
	          inner join modules.falta_geral on (falta_aluno.id = falta_geral.falta_aluno_id)
		       where falta_aluno.matricula_id = matricula.cod_matricula
		         and etapa = '1')
		 else
		     (select quantidade
		        from modules.falta_aluno
		  inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
		       where falta_aluno.matricula_id = matricula.cod_matricula
			 and etapa = '1'
			 and componente_curricular_id = view_componente_curricular.id)
	    end) as falta_etapa1,
           ncc2.nota_arredondada as nota_etapa_2,
           (case when tipo_falta = 1
		 then
		     (select quantidade
		        from modules.falta_aluno
	          inner join modules.falta_geral on (falta_aluno.id = falta_geral.falta_aluno_id)
		       where falta_aluno.matricula_id = matricula.cod_matricula
		         and etapa = '2')
		 else
		     (select quantidade
		        from modules.falta_aluno
		  inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
		       where falta_aluno.matricula_id = matricula.cod_matricula
			 and etapa = '2'
			 and componente_curricular_id = view_componente_curricular.id)
	    end) as falta_etapa2,
           ncc3.nota_arredondada as nota_etapa_3,
	   (case when tipo_falta = 1
		 then
		     (select quantidade
		        from modules.falta_aluno
	          inner join modules.falta_geral on (falta_aluno.id = falta_geral.falta_aluno_id)
		       where falta_aluno.matricula_id = matricula.cod_matricula
		         and etapa = '3')
		 else
		     (select quantidade
		        from modules.falta_aluno
		  inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
		       where falta_aluno.matricula_id = matricula.cod_matricula
			 and etapa = '3'
			 and componente_curricular_id = view_componente_curricular.id)
	    end) as falta_etapa3,
           ncc4.nota_arredondada as nota_etapa_4,
	   (case when tipo_falta = 1
		 then
		     (select quantidade
		        from modules.falta_aluno
	          inner join modules.falta_geral on (falta_aluno.id = falta_geral.falta_aluno_id)
		       where falta_aluno.matricula_id = matricula.cod_matricula
		         and etapa = '4')
		 else
		     (select quantidade
		        from modules.falta_aluno
		  inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
		       where falta_aluno.matricula_id = matricula.cod_matricula
			 and etapa = '4'
			 and componente_curricular_id = view_componente_curricular.id)
	    end) as falta_etapa4,
           nota_componente_curricular_media.media_arredondada as resultado_final,
           (select coalesce((select quantidade
			       from modules.falta_aluno
			 inner join modules.falta_geral on (falta_aluno.id = falta_geral.falta_aluno_id)
			      where falta_aluno.matricula_id = matricula.cod_matricula
				and etapa = '1'), 0) +
		   coalesce((select quantidade
			       from modules.falta_aluno
			 inner join modules.falta_geral on (falta_aluno.id = falta_geral.falta_aluno_id)
			      where falta_aluno.matricula_id = matricula.cod_matricula
				and etapa = '2'), 0) +
		    coalesce((select quantidade
		                from modules.falta_aluno
	                  inner join modules.falta_geral on (falta_aluno.id = falta_geral.falta_aluno_id)
		               where falta_aluno.matricula_id = matricula.cod_matricula
		                 and etapa = '3'), 0) +
		    coalesce((select quantidade
		                from modules.falta_aluno
	                  inner join modules.falta_geral on (falta_aluno.id = falta_geral.falta_aluno_id)
		               where falta_aluno.matricula_id = matricula.cod_matricula
		                 and etapa = '4'), 0)) as soma_falta_geral,
           (select coalesce((select quantidade
		               from modules.falta_aluno
			 inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
			      where falta_aluno.matricula_id = matricula.cod_matricula
				and etapa = '1'
				and componente_curricular_id = view_componente_curricular.id), 0) +
		   coalesce((select quantidade
		               from modules.falta_aluno
			 inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
			      where falta_aluno.matricula_id = matricula.cod_matricula
				and etapa = '2'
				and componente_curricular_id = view_componente_curricular.id), 0) +
		   coalesce((select quantidade
		               from modules.falta_aluno
			 inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
			      where falta_aluno.matricula_id = matricula.cod_matricula
				and etapa = '3'
				and componente_curricular_id = view_componente_curricular.id), 0) +
		   coalesce((select quantidade
		               from modules.falta_aluno
			 inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
			      where falta_aluno.matricula_id = matricula.cod_matricula
				and etapa = '4'
				and componente_curricular_id = view_componente_curricular.id), 0)) as soma_falta_componente_curricular,
	       (select sum(quantidade)
		 from modules.falta_aluno
	   inner join modules.falta_componente_curricular on (falta_aluno.id = falta_componente_curricular.falta_aluno_id)
		where falta_aluno.matricula_id = matricula.cod_matricula) as somatorio_faltas_cc,
	round((modules.frequencia_da_matricula(matricula.cod_matricula))::decimal, 2) as frequencia_geral,
           (case when tipo_falta = 2
                 then round((modules.frequencia_por_componente(matricula.cod_matricula, view_componente_curricular.id, turma.cod_turma))::decimal, 2)
		 else null end) as freq_cc,
	   (case when curso.padrao_ano_escolar = 0
		 then (select sequencial from pmieducar.turma_modulo where ref_cod_turma = turma.cod_turma order by sequencial desc limit 1)
		 else (select sequencial from pmieducar.ano_letivo_modulo where ref_ref_cod_escola = escola.cod_escola and ref_ano = {$ano} order by sequencial desc limit 1)
	   end) as qtde_etapas
      from pmieducar.instituicao
inner join pmieducar.escola on (instituicao.cod_instituicao = escola.ref_cod_instituicao)
inner join relatorio.view_dados_escola on (escola.cod_escola = view_dados_escola.cod_escola)
inner join pmieducar.matricula on (escola.cod_escola = matricula.ref_ref_cod_escola)
inner join pmieducar.curso on (matricula.ref_cod_curso = curso.cod_curso)
inner join pmieducar.serie on (matricula.ref_ref_cod_serie = serie.cod_serie)
JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id)
inner join pmieducar.matricula_turma on (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
inner join pmieducar.turma on (matricula_turma.ref_cod_turma = turma.cod_turma)
inner join pmieducar.aluno on (matricula.ref_cod_aluno = aluno.cod_aluno)
inner join cadastro.pessoa as nome_aluno on (aluno.ref_idpes = nome_aluno.idpes)
inner join relatorio.view_situacao on (matricula.cod_matricula = view_situacao.cod_matricula
				       and turma.cod_turma = view_situacao.cod_turma
				       and matricula_turma.sequencial = view_situacao.sequencial
				       and view_situacao.cod_situacao = 4)
left join pmieducar.transferencia_solicitacao on (matricula.cod_matricula = transferencia_solicitacao.ref_cod_matricula_saida)
left join pmieducar.transferencia_tipo on (transferencia_solicitacao.ref_cod_transferencia_tipo = transferencia_tipo.cod_transferencia_tipo)
inner join relatorio.view_componente_curricular on (true
	and turma.cod_turma = view_componente_curricular.cod_turma
	and view_componente_curricular.cod_serie = serie.cod_serie
)
left join modules.parecer_aluno on (parecer_aluno.matricula_id = matricula.cod_matricula)
left join modules.nota_aluno on (matricula.cod_matricula = nota_aluno.matricula_id)
left join modules.nota_componente_curricular as ncc1 on (nota_aluno.id = ncc1.nota_aluno_id
							  and view_componente_curricular.id = ncc1.componente_curricular_id
							  and ncc1.etapa = '1')
left join modules.nota_componente_curricular as ncc2 on (nota_aluno.id = ncc2.nota_aluno_id
							  and view_componente_curricular.id = ncc2.componente_curricular_id
							  and ncc2.etapa = '2')
left join modules.nota_componente_curricular as ncc3 on (nota_aluno.id = ncc3.nota_aluno_id
							  and view_componente_curricular.id = ncc3.componente_curricular_id
							  and ncc3.etapa = '3')
left join modules.nota_componente_curricular as ncc4 on (nota_aluno.id = ncc4.nota_aluno_id
							  and view_componente_curricular.id = ncc4.componente_curricular_id
							  and ncc4.etapa = '4')
left join modules.nota_componente_curricular_media on (nota_aluno.id = nota_componente_curricular_media.nota_aluno_id
				           and view_componente_curricular.id = nota_componente_curricular_media.componente_curricular_id)
left join modules.falta_aluno on (matricula.cod_matricula = falta_aluno.matricula_id)
     where matricula.ano = {$ano}
       and instituicao.cod_instituicao = {$instituicao}
       and matricula.ref_ref_cod_escola = {$escola}
       and matricula.ref_cod_curso = {$curso}
       and matricula.ref_ref_cod_serie = {$serie}
       and turma.cod_turma = {$turma}
       and COALESCE(matricula_turma.transferido, false) = true
       and (case when {$transferido} = 0 then true else matricula.cod_matricula = {$transferido} end)
       and (case when (select ts.cod_transferencia_solicitacao
			 from pmieducar.transferencia_solicitacao as ts
			where ts.ref_cod_matricula_saida = matricula.cod_matricula
		              order by cod_transferencia_solicitacao desc limit 1)::boolean
                       then transferencia_solicitacao.cod_transferencia_solicitacao = (select max(ts.cod_transferencia_solicitacao)
						        from pmieducar.transferencia_solicitacao as ts
						      where ts.ref_cod_matricula_saida = matricula.cod_matricula) else true end)
  order by nome_aluno.nome";
    }
}
