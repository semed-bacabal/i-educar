<?php

use iEducar\Modules\Reports\QueryFactory\MovimentoMensalQueryFactory;

return new class extends clsListagem
{
    public function Gerar()
    {
        $params = [];

        $params['ano'] = $this->getQueryString(name: 'ano');
        $params['instituicao'] = $this->getQueryString(name: 'ref_cod_instituicao');
        $params['escola'] = $this->getQueryString(name: 'ref_cod_escola');
        $params['curso'] = $this->getQueryString(name: 'ref_cod_curso');
        $params['serie'] = $this->getQueryString(name: 'ref_cod_serie');
        $params['turma'] = $this->getQueryString(name: 'ref_cod_turma');
        $params['data_inicial'] = $this->getQueryString(name: 'data_inicial');
        $params['data_final'] = $this->getQueryString(name: 'data_final');
        $params['modalidade'] = $this->getQueryString(name: 'modalidade');

        $this->breadcrumb(currentPage: 'Consulta de movimento mensal', breadcrumbs: ['educar_index.php' => 'Escola']);

        $required = [
            'ano',
            'instituicao',
            'escola',
            'data_inicial',
            'data_final',
        ];

        foreach ($required as $req) {
            if (empty($params[$req])) {
                $this->simpleRedirect(url: '/intranet/educar_index.php');
            }
        }

        $params['data_inicial'] = Portabilis_Date_Utils::brToPgSQL(date: $params['data_inicial']);
        $params['data_final'] = Portabilis_Date_Utils::brToPgSQL(date: $params['data_final']);

        $startDate = [];
        $endDate = [];

        foreach ($this->getQueryString(name: 'calendars') as $datas) {
            $arrayDatas = explode(separator: ' ', string: $datas);
            $startDate[] = $arrayDatas[0];
            $endDate[] = $arrayDatas[1];
        }

        $params['data_inicial_calendario'] = $startDate ?: null;
        $params['data_final_calendario'] = $endDate ?: null;

        $base = new clsBanco;
        $base->FraseConexao();
        $connectionString = 'pgsql:' . $base->getFraseConexao();
        $data = (new MovimentoMensalQueryFactory(connection: new \PDO(dsn: $connectionString), params: $params))
            ->getData();

        $this->titulo = 'Parâmetros';
        $this->acao = 'go("/intranet/educar_consulta_movimento_mensal.php")';
        $this->nome_acao = 'Nova consulta';

        $escola = 'Todas';
        $curso = 'Todos';
        $serie = 'Todas';
        $turma = 'Todas';

        if (!empty($params['escola'])) {
            $dados = (array) Portabilis_Utils_Database::fetchPreparedQuery(sql: "
                select
                    juridica.fantasia
                from
                    pmieducar.escola
                inner join
                    cadastro.juridica on juridica.idpes = escola.ref_idpes
                where true
                    and escola.cod_escola = {$params['escola']}
                limit 1;
            ");

            $escola = $dados[0]['fantasia'];
        }

        if (!empty($params['curso'])) {
            $dados = (array) Portabilis_Utils_Database::fetchPreparedQuery(
                sql: "select nm_curso from pmieducar.curso where cod_curso = {$params['curso']};"
            );

            $curso = $dados[0]['nm_curso'];
        }

        if (!empty($params['serie'])) {
            $dados = (array) Portabilis_Utils_Database::fetchPreparedQuery(
                sql: "select nm_serie from pmieducar.serie where cod_serie = {$params['serie']};"
            );

            $serie = $dados[0]['nm_serie'];
        }

        if (!empty($params['turma'])) {
            $dados = (array) Portabilis_Utils_Database::fetchPreparedQuery(
                sql: "select nm_turma from pmieducar.turma where cod_turma = {$params['turma']};"
            );

            $turma = $dados[0]['nm_turma'];
        }

        $this->addCabecalhos(coluna: [
            'Ano',
            'Escola',
            'Curso',
            'Série',
            'Turma',
            'Data inicial',
            'Data final',
        ]);

        $this->addLinhas(linha: [
            filter_var(value: $params['ano'], filter: FILTER_SANITIZE_STRING),
            $escola,
            $curso,
            $serie,
            $turma,
            filter_var(value: $this->getQueryString(name: 'data_inicial'), filter: FILTER_SANITIZE_STRING),
            filter_var(value: $this->getQueryString(name: 'data_final'), filter: FILTER_SANITIZE_STRING),
        ]);

        $linkTemplate = '<a href="#" class="mostra-consulta" style="font-weight: bold;" data-api="ConsultaMovimentoMensal" data-params=\'%s\' data-tipo="%s">%d</a>';

        foreach ($data as $key => $value) {
            foreach ($value as $k => $v) {
                switch ($k) {
                    case 'cod_serie':
                    case 'nm_serie':
                    case 'nm_turma':
                    case 'turno':
                    case 'mat_ini_t':
                    case 'mat_final_m':
                    case 'mat_final_f':
                    case 'mat_final_t':
                        continue;
                    default:
                        $paramsCopy = $params;
                        $paramsCopy['serie'] = $value['cod_serie'];
                        $paramsCopy['turma'] = $value['cod_turma'];
                        $paramsCopy = json_encode(value: $paramsCopy);
                        $data[$key][$k] = sprintf($linkTemplate, $paramsCopy, $k, $v);
                }
            }
        }

        $data = json_encode(value: $data);

        $tableScript = <<<JS
(function () {
  let paramsTable = document.querySelectorAll('#form_resultado .tablelistagem')[0];
  paramsTable.setAttribute('style', 'width: 100%;');

  let data = {$data};
  let table = [];

  table.push('<table class="tablelistagem" style="width: 100%; margin-bottom: 100px;" cellspacing="1" cellpadding="4" border="0">');
    table.push('<tr>');
      table.push('<td class="titulo-tabela-listagem" colspan="25">Resultados</td>');
    table.push('</tr>');

    table.push('<tr>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="4">Série</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="4">Turma</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="4">Turno</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="3">Matrícula inicial</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="16">Alunos</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="3">Matrícula final</td>');
    table.push('</tr>');

    table.push('<tr>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="3">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="3">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="3">T</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="2" rowspan="2">Transf.</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="2" rowspan="2">Deixou de Freq.</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="2" rowspan="2">Admitido</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="2" rowspan="2">Óbito</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="4">Reclassificado</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="4">Remanejado</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="3">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="3">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" rowspan="3">T</td>');
    table.push('</tr>');

    table.push('<tr>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="2">saiu</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="2">entrou</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="2">saiu</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;" colspan="2">entrou</td>');
    table.push('</tr>');

    table.push('<tr>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">F</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">M</td>');
      table.push('<td class="formdktd" style="font-weight: bold; text-align: center;">F</td>');
    table.push('</tr>');

  for (let i = 0; i < data.length; i++) {
    let item = data[i];
    let cellClass = ((i % 2) === 0) ? 'formlttd' : 'formmdtd';

    table.push('<tr>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.nm_serie + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.nm_turma + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.turno + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_ini_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_ini_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_ini_t + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_transf_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_transf_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_aband_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_aband_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_admit_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_admit_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_falecido_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_falecido_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_reclassificados_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_reclassificados_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_reclassificadose_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_reclassificadose_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_trocas_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_trocas_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_trocae_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_trocae_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_final_m + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_final_f + '</td>');
      table.push('<td class="' + cellClass + '" valign="top" align="left">' +  item.mat_final_t + '</td>');
    table.push('</tr>');
  }

  table.push('</table>');

  let base = document.querySelectorAll('#corpo')[0];
  let wrapper= document.createElement('div');
  wrapper.innerHTML = table.join('');
  let tableObj = wrapper.firstChild;

  base.appendChild(tableObj);
})();
JS;

        Portabilis_View_Helper_Application::embedJavascript(viewInstance: $this, script: $tableScript, afterReady: false);
        Portabilis_View_Helper_Application::loadJavascript(viewInstance: $this, files: ['/intranet/scripts/consulta_movimentos.js']);
    }

    public function Formular()
    {
        $this->title = 'Consulta de movimento mensal';
        $this->processoAp = 9998910;
    }
};
