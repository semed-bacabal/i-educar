<?php

use App\Models\LegacySchoolAcademicYear;

$obj_permissoes = new clsPermissoes;
$nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);
$retorno .= '<tr>
                     <td height="24" colspan="2" class="formdktd">
                     <span class="form">
                     <b style="font-size: 16px;">Filtros de busca</b>
                     </span>
                     </td>
                     </tr>';

$retorno .= '<form action="" method="post" id="formcadastro" name="formcadastro">';
if ($obrigatorio) {
    $instituicao_obrigatorio = $escola_obrigatorio = $curso_obrigatorio = $serie_obrigatorio = $turma_obrigatorio = true;
} else {
    $instituicao_obrigatorio = isset($instituicao_obrigatorio) ? $instituicao_obrigatorio : $obrigatorio;
    $escola_obrigatorio = isset($escola_obrigatorio) ? $escola_obrigatorio : $obrigatorio;
    $curso_obrigatorio = isset($curso_obrigatorio) ? $curso_obrigatorio : $obrigatorio;
    $serie_obrigatorio = isset($serie_obrigatorio) ? $serie_obrigatorio : $obrigatorio;
    $turma_obrigatorio = isset($turma_obrigatorio) ? $turma_obrigatorio : $obrigatorio;
}

if ($desabilitado) {
    $instituicao_desabilitado = $escola_desabilitado = $curso_desabilitado = $serie_desabilitado = $turma_desabilitado = true;
} else {
    $instituicao_desabilitado = isset($instituicao_desabilitado) ? $instituicao_desabilitado : $desabilitado;
    $escola_desabilitado = isset($escola_desabilitado) ? $escola_desabilitado : $desabilitado;
    $curso_desabilitado = isset($curso_desabilitado) ? $curso_desabilitado : $desabilitado;
    $serie_desabilitado = isset($serie_desabilitado) ? $serie_desabilitado : $desabilitado;
    $turma_desabilitado = isset($turma_desabilitado) ? $turma_desabilitado : $desabilitado;
}

$obj_permissoes = new clsPermissoes;
$nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);

$opcoes = ['' => 'Selecione'];
$obj_instituicao = new clsPmieducarInstituicao;
$obj_instituicao->setCamposLista('cod_instituicao, nm_instituicao');
$obj_instituicao->setOrderby('nm_instituicao ASC');
$lista = $obj_instituicao->lista(int_ativo: 1);
if (is_array($lista) && count($lista)) {
    foreach ($lista as $registro) {
        $opcoes["{$registro['cod_instituicao']}"] = "{$registro['nm_instituicao']}";
    }
}
if ($get_escola && $get_curso) {
    $retorno .= '<tr id="tr_status" class="input_quadro_horario">
                         <td valign="top" class="formlttd">
                         <span class="form">Instituição</span>
                         <span class="campo_obrigatorio">*</span>
                         <br/>
                         <sub style="vertical-align: top;"/>
                         </td>';
    $retorno .= '<td valign="top" class="formlttd"><span class="form">';
    $retorno .= '<select onchange="getEscola();" class=\'geral\' name=\'ref_cod_instituicao\' id=\'ref_cod_instituicao\'>';
    reset($opcoes);
    foreach ($opcoes as $chave => $texto) {
        $retorno .= '<option id="ref_cod_instituicao_'.urlencode($chave).'" value="'.urlencode($chave).'"';

        if ($chave == $this->ref_cod_instituicao) {
            $retorno .= ' selected';
        }
        $retorno .= ">$texto</option>";
    }
    $retorno .= '</select>';
    $retorno .= '</span>
                            </td>
                            </tr>';
} else {
    $retorno .= '<tr id="tr_status" class="input_quadro_horario">
                         <td valign="top" class="formlttd">
                         <span class="form">Instituição</span>
                         <span class="campo_obrigatorio">*</span>
                         <br/>
                         <sub style="vertical-align: top;"/>
                         </td>';
    $retorno .= '<td valign="top" class="formlttd"><span class="form">';
    $retorno .= '<select class=\'geral\' name=\'ref_cod_instituicao\' id=\'ref_cod_instituicao\'>';

    reset($opcoes);
    foreach ($opcoes as $chave => $texto) {
        $retorno .= '<option id="ref_cod_instituicao_'.urlencode($chave).'" value="'.urlencode($chave).'"';

        if ($chave == $this->ref_cod_instituicao) {
            $retorno .= ' selected';
        }
        $retorno .= ">$texto</option>";
    }
    $retorno .= '</select>';
    $retorno .= '</span>
                            </td>
                            </tr>';
}

if ($get_escola) {
    $opcoes_escola = ['' => 'Selecione'];
    $obj_escola = new clsPmieducarEscola;
    $lista = $obj_escola->lista(str_nome: 1);
    if ($nivel_usuario == 4 || $nivel_usuario == 8) {
        $opcoes_escola = ['' => 'Selecione'];
        $obj_escola = new clsPmieducarEscolaUsuario;
        $lista = $obj_escola->lista($this->pessoa_logada);

        if (is_array($lista) && count($lista)) {
            foreach ($lista as $registro) {
                $codEscola = $registro['ref_cod_escola'];

                $escola = new clsPmieducarEscola($codEscola);
                $escola = $escola->detalhe();

                $opcoes_escola[$codEscola] = $escola['nome'];
            }
        }
    } elseif ($this->ref_cod_instituicao) {
        $opcoes_escola = ['' => 'Selecione'];
        $obj_escola = new clsPmieducarEscola;
        $lista = $obj_escola->lista(int_ref_cod_instituicao: $this->ref_cod_instituicao, str_nome: 1);
        if (is_array($lista) && count($lista)) {
            foreach ($lista as $registro) {
                $opcoes_escola["{$registro['cod_escola']}"] = "{$registro['nome']}";
            }
        }
    }
    if ($get_escola) {
        $retorno .= '<tr id="tr_escola" class="input_quadro_horario">
                             <td valign="top" class="formmdtd">
                             <span class="form">Escola</span>
                             <span class="campo_obrigatorio">*</span>
                             <br/>
                             <sub style="vertical-align: top;"/>
                             </td>';
        $retorno .= '<td valign="top" class="formmdtd"><span class="form">';

        $disabled = !$this->ref_cod_escola && $nivel_usuario == 1 /* && !$this->ref_cod_curso */ ? 'disabled=\'true\' ' : '';
        $retorno .= " <select onchange=\"getCurso();getAnoLetivo();\" class='geral' name='ref_cod_escola' {$disabled} id='ref_cod_escola'>";

        reset($opcoes_escola);
        foreach ($opcoes_escola as $chave => $texto) {
            $retorno .= '<option id="ref_cod_escola_'.urlencode($chave).'" value="'.urlencode($chave).'"';

            if ($chave == $this->ref_cod_escola) {
                $retorno .= ' selected';
            }
            $retorno .= ">$texto</option>";
        }
        $retorno .= '</select>';
        $retorno .= '</span>
                                </td>
                                </tr>';
    }
}
if ($get_ano) {
    $opcoes_ano = ['' => 'Selecione'];

    // EDITAR
    if ($this->ref_cod_escola) {
        $lst_esc_ano = LegacySchoolAcademicYear::query()->whereSchool($this->ref_cod_escola)->active()->get(['ano']);
        if ($lst_esc_ano->isNotEmpty()) {
            foreach ($lst_esc_ano as $detalhe) {
                $opcoes_ano["{$detalhe['ano']}"] = "{$detalhe['ano']}";
            }
        }
    }
    $retorno .= '<tr id="tr_ano" class="input_quadro_horario">
                         <td valign="top" class="formlttd">
                         <span class="form">Ano</span>
                         <span class="campo_obrigatorio">*</span>
                         <br/>
                         <sub style="vertical-align: top;"/>
                         </td>';
    $retorno .= '<td valign="top" class="formlttd"><span class="form">';

    $disabled = !$this->ano && $nivel_usuario == 1 ? 'disabled=\'true\' ' : '';
    $retorno .= " <select onchange=\"getSerie();\" class='geral' name='ano' {$disabled} id='ano'>";

    reset($opcoes_ano);
    foreach ($opcoes_ano as $chave => $texto) {
        $retorno .= '<option id="ano'.urlencode($chave).'" value="'.urlencode($chave).'"';

        if ($chave == $this->ano) {
            $retorno .= ' selected';
        }
        $retorno .= ">$texto</option>";
    }
    $retorno .= '</select>';
    $retorno .= '</span>
                            </td>
                            </tr>';
}
if ($get_curso) {
    $opcoes_curso = ['' => 'Selecione'];

    // EDITAR
    if ($this->ref_cod_escola) {
        $obj_esc_cur = new clsPmieducarEscolaCurso;
        $lst_esc_cur = $obj_esc_cur->lista(int_ref_cod_escola: $this->ref_cod_escola, int_ativo: 1);
        if (is_array($lst_esc_cur) && count($lst_esc_cur)) {
            foreach ($lst_esc_cur as $detalhe) {
                $opcoes_curso["{$detalhe['ref_cod_curso']}"] = "{$detalhe['nm_curso']}";
            }
        }
    }
    $retorno .= '<tr id="tr_curso" class="input_quadro_horario">
                         <td valign="top" class="formlttd">
                         <span class="form">Curso</span>
                         <span class="campo_obrigatorio">*</span>
                         <br/>
                         <sub style="vertical-align: top;"/>
                         </td>';
    $retorno .= '<td valign="top" class="formlttd"><span class="form">';

    $disabled = !$this->ref_cod_curso && $nivel_usuario == 1 /* && !$this->ref_cod_curso */ ? 'disabled=\'true\' ' : '';
    $retorno .= " <select onchange=\"getSerie();\" class='geral' name='ref_cod_curso' {$disabled} id='ref_cod_curso'>";

    reset($opcoes_curso);
    foreach ($opcoes_curso as $chave => $texto) {
        $retorno .= '<option id="ref_cod_curso_'.urlencode($chave).'" value="'.urlencode($chave).'"';

        if ($chave == $this->ref_cod_curso) {
            $retorno .= ' selected';
        }
        $retorno .= ">$texto</option>";
    }

    $retorno .= '</select>';
    $retorno .= '</span>
                            </td>
                            </tr>';
}
if ($get_serie) {
    $opcoes_serie = ['' => 'Selecione'];
    // EDITAR
    if ($this->ref_cod_curso && $this->ref_cod_escola) {
        $obj_serie = new clsPmieducarSerie;
        $obj_serie->setOrderby('nm_serie ASC');
        $lst_serie = $obj_serie->lista(int_ref_cod_curso: $this->ref_cod_curso, int_ativo: 1);
        if (is_array($lst_serie) && count($lst_serie)) {
            foreach ($lst_serie as $serie) {
                $opcoes_serie["{$serie['cod_serie']}"] = $serie['nm_serie'];
            }
        }
    }
    $retorno .= '<tr id="tr_curso" class="input_quadro_horario">
                         <td valign="top" class="formmdtd">
                         <span class="form">Série</span>
                         <span class="campo_obrigatorio">*</span>
                         <br/>
                         <sub style="vertical-align: top;"/>
                         </td>';
    $retorno .= '<td valign="top" class="formmdtd"><span class="form">';

    $disabled = !$this->ref_cod_serie && $nivel_usuario == 1 /* && !$this->ref_cod_curso */ ? 'disabled=\'true\' ' : '';
    $retorno .= " <select onchange=\"getTurma();\" class='geral' name='ref_cod_serie' {$disabled} id='ref_cod_serie'>";

    reset($opcoes_serie);
    foreach ($opcoes_serie as $chave => $texto) {
        $retorno .= '<option id="ref_cod_serie_'.urlencode($chave).'" value="'.urlencode($chave).'"';

        if ($chave == $this->ref_cod_serie) {
            $retorno .= ' selected';
        }
        $retorno .= ">$texto</option>";
    }
    $retorno .= '</select>';
    $retorno .= '</span>
                            </td>
                            </tr>';
}
if ($get_turma) {
    $opcoes_turma = ['' => 'Selecione'];
    // EDITAR
    if ($this->ref_cod_serie /* || $this->ref_cod_curso */) {
        $obj_turma = new clsPmieducarTurma;
        $obj_turma->setOrderby('nm_turma ASC');
        $lst_turma = $obj_turma->lista(int_ref_ref_cod_serie: $this->ref_cod_serie, int_ref_ref_cod_escola: $this->ref_cod_escola, int_ref_cod_curso: $this->ref_cod_curso, ano: $this->ano);
        if (is_array($lst_turma) && count($lst_turma)) {
            foreach ($lst_turma as $turma) {
                $opcoes_turma["{$turma['cod_turma']}"] = $turma['nm_turma'];
            }
        }
    }
    $retorno .= '<tr id="tr_turma" class="input_quadro_horario">
                         <td valign="top" class="formlttd">
                         <span class="form">Turma</span>
                         <span class="campo_obrigatorio">*</span>
                         <br/>
                         <sub style="vertical-align: top;"/>
                         </td>';
    $retorno .= '<td valign="top" class="formlttd"><span class="form">';

    $disabled = (!$this->ref_cod_turma && $nivel_usuario == 1) ? 'disabled=\'true\' ' : '';
    $retorno .= " <select onchange=\"\" class='geral' name='ref_cod_turma' {$disabled} id='ref_cod_turma'>";

    reset($opcoes_turma);
    foreach ($opcoes_turma as $chave => $texto) {
        $retorno .= '<option id="ref_cod_turma_'.urlencode($chave).'" value="'.urlencode($chave).'"';

        if ($chave == $this->ref_cod_turma) {
            $retorno .= ' selected';
        }
        $retorno .= ">$texto</option>";
    }

    $retorno .= '</select>';
    $retorno .= '</span>
                            </td>
                            </tr>';
}
if (isset($get_cabecalho)) {
    if ($nivel_usuario == 1 || $nivel_usuario == 2 || $nivel_usuario == 4) {
        ${$get_cabecalho}[] = 'Curso';
        ${$get_cabecalho}[] = 'Série';
        ${$get_cabecalho}[] = 'Turma';
    }
    if ($nivel_usuario == 1 || $nivel_usuario == 2) {
        ${$get_cabecalho}[] = 'Escola';
    }
    if ($nivel_usuario == 1) {
        ${$get_cabecalho}[] = 'Instituição';
    }
}

$validacao = 'if ( !document.getElementById( "ref_cod_instituicao" ).value ) {
                alert( "Por favor, selecione uma instituição" );
                return false;
                }
                if ( !document.getElementById( "ref_cod_escola" ).value) {
                    //if( !document.getElementById( "ref_cod_curso" ).value){
                        alert( "Por favor, selecione uma escola" );
                        return false;
                    //}
                }
                if ( !document.getElementById( "ano" ).value ) {
                alert( "Por favor, selecione um ano" );
                return false;
                }
                if ( !document.getElementById( "ref_cod_curso" ).value ) {
                alert( "Por favor, selecione um curso" );
                return false;
                }
                if ( !document.getElementById( "ref_cod_serie" ).value) {
                    //if( document.getElementById( "ref_cod_escola" ).value){
                        alert( "Por favor, selecione uma série" );
                        return false;
                //  }else{
                    //  alert( "Por favor, selecione uma turma" );
                //      return false;
                //  }
                }
                if ( !document.getElementById( "ref_cod_turma" ).value ) {
                alert( "Por favor, selecione uma turma" );
                return false;
                } ';
$retorno .= '</form>';
$retorno .= "<tr>
                     <td colspan='2' class='formdktd'/>
                     </tr>
                     <tr>
                     <td align='center' colspan='2'>
                     <script language='javascript'>
                     function acao() {
                     {$validacao}
                     document.formcadastro.submit();
                     }
                     </script>
                     <input type='button' class='btn-green' id='botao_busca' value='Buscar' onclick='javascript:acao();' class='botaolistagem'/>
                     </td>
                     </tr><tr><td>&nbsp;</td></tr>";
?>
<script>

<?php
if ($nivel_usuario == 1 || $nivel_usuario == 2) {
    ?>
    function getEscola( escolas )
    {

        if(escolas.length)
        {
            setAttributes(campoEscola,'Selecione uma escola',false);

            $j.each(escolas, function(i, item) {
                campoEscola.options[campoEscola.options.length] = new Option(item.name,item.id, false, false);
            });
        }
        else
            campoEscola.options[0].text = 'A instituição não possui nenhuma escola';
    }
<?php
}
?>

function getCurso(cursos)
{
    if(cursos.length)    {
        setAttributes(campoCurso,'Selecione um curso',false);
        $j.each(cursos, function(i, item) {
            campoCurso.options[campoCurso.options.length] = new Option(item.name,item.id, false, false);
        });
    }
    else
        campoCurso.options[0].text = 'A escola não possui nenhum curso';
}

function getAnoLetivo(anos)
{
    if(anos.length)
    {
        setAttributes(campoAno,'Selecione um ano',false);

        $j.each(anos, function(i, item) {
            campoAno.options[campoAno.options.length] = new Option(item.year,item.year, false, false);
        });
    }
    else
        campoAno.options[0].text = 'A escola não possui nenhum ano';
}

function getSerie(series)
{
    if(series.length)
    {
        setAttributes(campoSerie,'Selecione uma série',false);

        $j.each(series, function(i, item) {
            campoSerie.options[campoSerie.options.length] = new Option(item.name,item.id, false, false);
        });
    }
    else
        campoSerie.options[0].text = 'A escola/curso não possui nenhuma série';
}

function getTurma(turmas)
{

    if(turmas.length)
    {
        setAttributes(campoTurma,'Selecione uma turma',false);

        $j.each(turmas, function(i, item) {
            campoTurma.options[campoTurma.options.length] = new Option(item.name,item.id, false, false);
        });
    }
    else
        campoTurma.options[0].text = 'A escola/série não possui nenhuma turma';
}
</script>
