var rebuildAllChosenAnosLetivos = undefined;
function existeComponente(){
    if ($j('input[name^="disciplinas["]:checked').length <= 0) {
        alert('É necessário adicionar pelo menos um componente curricular.');
        return false;
    }
    return true;
}

document.getElementById('ref_cod_instituicao').onchange = function () {
    getDuploEscolaCurso();
}

document.getElementById('ref_cod_escola').onchange = function () {
    let xml;
    let campoEscola;
    const campoInstituicao = document.getElementById('ref_cod_instituicao').value;
    const campoCurso = document.getElementById('ref_cod_curso');
    const campoAno = document.getElementById('ano').value;
    if (document.getElementById('ref_cod_escola')) {
      campoEscola = document.getElementById('ref_cod_escola').value;
    } else if (document.getElementById('ref_ref_cod_escola')) {
      campoEscola = document.getElementById('ref_ref_cod_escola').value;
    }
    campoCurso.length = 1;

    limpaCampos(3);
    if (campoEscola) {
      campoCurso.disabled = true;
      campoCurso.options[0].text = 'Carregando cursos';

      getApiResource("/api/resource/course",atualizaLstEscolaCurso,{school:campoEscola, year_eq:campoAno});
    } else {
      getApiResource("/api/resource/course",atualizaLstCurso,{institution:campoInstituicao});
    }
}

document.getElementById('ref_cod_curso').onchange = function () {
    getSerie();

    var campoDisciplinas = document.getElementById('disciplinas');
    campoDisciplinas.innerHTML = "Nenhuma série selecionada";
}

function getDisciplina(disciplinas) {
  const campoDisciplinas = document.getElementById('disciplinas');
  var conteudo = '';

    if (disciplinas.length) {
        const first_key = disciplinas[0].id;

        conteudo += '<div style="margin-bottom: 10px; float: left">';
        conteudo += '  <span style="display: block; float: left; width: 250px;">Nome</span>';
        conteudo += '  <span style="display: block; float: left; width: 100px">Carga horária</span>';
        conteudo += '  <span style="display: block; float: left; width: 180px;">Usar padrão do componente?</span>';
        conteudo += '  <span style="display: block; float: left; width: 180px;">Hora falta</span>';
        conteudo += '  <span style="display: block; float: left; width: 150px;">Anos letivos</span>';
        conteudo += '</div>';

        conteudo += '<br style="clear: left" />';
        conteudo += '<div style="margin-bottom: 10px; float: left">';
        conteudo += "  <label style='display: block; float: left; width: 150px;'><input type='checkbox' name='CheckTodos' onClick='marcarCheck(" + '"disciplinas[]"' + ");'/>Marcar todos</label>";
        conteudo += "<label style='display: block; float: left; width: 100px'>&nbsp;</label>" +
        "<label style='display: block; float: left; width: 100px;'>" +
        "<a class='clone-values' onclick='cloneValues("+first_key+",\"carga_horaria\")'>" +
        "<i class='fa fa-clone' aria-hidden='true'></i>" +
        "</a>" +
        "</label>";
        conteudo += "  <label style='display: block; float: left; width: 150px'><input type='checkbox' name='CheckTodos2' onClick='marcarCheck(" + '"usar_componente[]"' + ");';/>Marcar todos</label>";
        conteudo += "<label style='display: block; float: left; width: 130px'>&nbsp;</label>" +
        "<label style='display: block; float: left; width: 100px;'>" +
        "<a class='clone-values' onclick='cloneValues("+first_key+",\"anos_letivos\")'>" +
        "<i class='fa fa-clone' aria-hidden='true'></i>" +
        "</a>" +
        "</label>";
        conteudo += '</div>';
        conteudo += '<br style="clear: left" />';

        $j.each(disciplinas, function(i, item) {
          id = item.id;

          conteudo += '<div style="margin-bottom: 10px; float: left">';
          conteudo += '  <label style="display: block; float: left; width: 250px;"><input type="checkbox" name="disciplinas[' + id + ']" class="check_'+id+'" id="disciplinas[]" value="' + id + '">' + item.name + '</label>';
          conteudo += '  <label style="display: block; float: left; width: 100px;"><input type="text" id="carga_horaria_' + id + '" data-id="'+id+'" name="carga_horaria[' + id + ']" class="carga_horaria" value="" size="5" maxlength="7"></label>';
          conteudo += '  <label style="display: block; float: left; width: 180px;"><input type="checkbox" id="usar_componente[]" name="usar_componente[' + id + ']" class="" value="1">(' + formatNumber(item.workload)  + ' h)</label>';
          conteudo += '  <label style="display: block; float: left; width: 100px;"><input type="text" id="hora_falta_' + id + '" data-id="'+id+'" name="hora_falta[' + id + ']" class="" value="" size="5" maxlength="7"></label>';
          conteudo += `
            <select name='componente_anos_letivos[${id}][]' class="anos_letivos" id='anos_letivos_${id}' data-id='${id}' style='width: 150px;' multiple='multiple'>
            </select>
            `;
          conteudo += '</div>';
          conteudo += '<br style="clear: left" />';
        });
    } else {
        campoDisciplinas.innerHTML = 'A série/ano escolar não possui componentes '
            + 'curriculares cadastrados.';
    }

    if (conteudo) {
        campoDisciplinas.innerHTML = '<table cellspacing="0" cellpadding="0" border="0">';
        campoDisciplinas.innerHTML += '<tr align="left"><td>' + conteudo + '</td></tr>';
        campoDisciplinas.innerHTML += '</table>';
    }
    rebuildAllChosenAnosLetivos();
}

function formatNumber(value)
{
  let float = Math.floor(value);
  let calc = value - float;

  if (calc > 0) {
    return value;
  }

  return Math.round(value);
}

document.getElementById('ref_cod_serie').onchange = function () {
    const campoSerie = document.getElementById('ref_cod_serie').value;

    const campoDisciplinas = document.getElementById('disciplinas');
    campoDisciplinas.innerHTML = "Carregando disciplina";

    getApiResource("/api/resource/discipline", getDisciplina,{grade:campoSerie});
};

after_getEscola = function () {
    getEscolaCurso();
    getSerie();

    var campoDisciplinas = document.getElementById('disciplinas');
    campoDisciplinas.innerHTML = "Nenhuma série selecionada";
};

function getSerie() {

    var campoCurso = document.getElementById('ref_cod_curso').value;

    var campoSerie = document.getElementById('ref_cod_serie');

    campoSerie.length = 1;

    limpaCampos(4);

    if(campoCurso) {
        campoSerie.disabled = true;
        campoSerie.options[0].text = 'Carregando séries';

        getApiResource("/api/resource/grade",atualizaLstSerie,{course:campoCurso});
    } else {
        campoSerie.options[0].text = 'Selecione';
    }
}

function atualizaLstSerie(series) {
    const campoSerie = document.getElementById('ref_cod_serie');
    setAttributes(campoSerie,'Selecione uma série',false)

    if (series.length) {
      $j.each(series, function(i, item) {
        campoSerie.options[campoSerie.options.length] = new Option(item.name,item.id, false, false);
      });
    } else {
        campoSerie.options[0].text = 'O curso não possui nenhuma série ou todas as séries já estão associadas a essa escola';
        campoSerie.disabled = true;
    }
}

function marcarCheck(idValue) {
    // testar com formcadastro
    var contaForm = document.formcadastro.elements.length;
    var campo = document.formcadastro;
    var i;

    if (idValue == 'disciplinas[]') {
        for (i = 0; i < contaForm; i++) {
            if (campo.elements[i].id == idValue) {
                campo.elements[i].checked = campo.CheckTodos.checked;
            }
        }
    } else if (idValue == 'usar_componente[]') {
        for (i = 0; i < contaForm; i++) {
            if (campo.elements[i].id == idValue) {
                campo.elements[i].checked = campo.CheckTodos2.checked;
            }
        }

    } else if (idValue == 'etapas_especificas[]') {
        for (i = 0; i < contaForm; i++) {
            if (campo.elements[i].id == idValue) {
                campo.elements[i].checked = campo.CheckTodos3.checked;
            }
        }
    } else if (idValue == 'usar_componente_hora_falta[]') {
        for (i = 0; i < contaForm; i++) {
          if (campo.elements[i].id == idValue) {
            campo.elements[i].checked = campo.CheckTodos4.checked;
          }
        }
    }
}

$j('.etapas_utilizadas').mask("9,9,9,9", {placeholder: "1,2,3..."});


var submitButton = $j('#btn_enviar');
submitButton.removeAttr('onclick');

function existeDispensa(componentes){
    var retorno = false;
    var serie = $j('#ref_cod_serie_').val();
    var escola = $j('#ref_cod_escola_').val();
    var url = getResourceUrlBuilder.buildUrl('/module/Api/ComponentesSerie',
        'existe-dispensa',
        {disciplinas : componentes,
            serie_id : serie,
           escola_id : escola});

    var options = {
        url      : url,
        dataType : 'json',
        async    : false,
        success  : function (dataResponse) {
            if(dataResponse.existe_dispensa){
                retorno = true;
                ModalDispensas.init('dispensas', dataResponse.dispensas);
            }
        }
    };
    getResource(options);
    return retorno;
}

function existeDependencia(componentes){
    var retorno = false;
    var serie = $j('#ref_cod_serie_').val();
    var escola = $j('#ref_cod_escola_').val();
    var url = getResourceUrlBuilder.buildUrl('/module/Api/ComponentesSerie',
                                             'existe-dependencia',
                                             {disciplinas : componentes,
                                              serie_id : serie,
                                              escola_id : escola});

    var options = {
        url      : url,
        dataType : 'json',
        async    : false,
        success  : function (dataResponse) {
            if(dataResponse.existe_dependencia){
                messageUtils.error('Não foi possível remover o componente. Existe registros de dependência neste componente.');
                retorno = true;
            }
        }
    };

    getResource(options);
    return retorno;
}

submitButton.click(function(){

    if (!existeComponente()) {
        return false;
    }

    var componentesInput = $j('[name*=disciplinas]');
    var arrayComponentes = [];

    componentesInput.each(function(i, input) {
        id = input.name.replace(/\D/g, '');
        check = $j('[name="disciplinas[' + id + ']"]').is(':checked');

        if (check) {
            arrayComponentes.push(id);
        }
    });

    if (existeDispensa(arrayComponentes) || existeDependencia(arrayComponentes)) {
        return false;
    }

    acao();

});

(function($){
  $(document).ready(function(){
    let ajustaDisciplinas = () => {
      let $tr = $('<tr/>');
      $tr.insertBefore($('#tr_disciplinas_'));
      $('#tr_disciplinas_ td').attr('colspan', '2');
      $('#tr_disciplinas_ td:first').appendTo($tr);
    };
    ajustaDisciplinas();

    $('#ref_cod_curso').on('change', function(){
      let codCurso = $(this).val();
      let codEscola = $('#ref_cod_escola').val();
      if (!codCurso || !codEscola) {
        $('#anos_letivos').val([]).empty().trigger('chosen:updated');
        return false;
      }
      let url = getResourceUrlBuilder.buildUrl(
        '/module/Api/EscolaCurso',
        'anos-letivos',
        { cod_curso : codCurso, cod_escola: codEscola }
      );

      var options = {
        url      : url,
        dataType : 'json',
        success  : function (dataResponse) {
          $('#anos_letivos').html(
            (dataResponse.anos_letivos||[]).map(ano => `<option value='${ano}'>${ano}</option>`).join()
          ).trigger('chosen:updated');
        }
      };
      getResource(options);
    });

    let reloadChosenAnosLetivos = ($element) => {
      if ($element.parent().find('.chosen-container').length > 0) {
          $element.chosen('destroy');
      }
      $element.chosen({
        no_results_text: "Sem resultados para ",
        width: '231px',
        placeholder_text_multiple: "Selecione as opções",
        placeholder_text_single: "Selecione uma opção"
      });
    }

    reloadChosenAnosLetivos($('select[name^=componente_anos_letivos]'));
    $('#anos_letivos').on('change', function(){
      $.each($('select[name^=componente_anos_letivos]'), function(){
        let oldValues = $(this).val();
        $(this).empty();
        $('#anos_letivos option:selected').clone().appendTo($(this));
        $(this).val(oldValues).trigger('chosen:updated');
      });
    });

    rebuildAllChosenAnosLetivos = function(){
      reloadChosenAnosLetivos($('select[name^=componente_anos_letivos]'));
      $('#anos_letivos').trigger('change');
    }
  });
})(jQuery);

$j('#ano_letivo').change(function() {
  var url = new URL(window.location.href);
  var query_string = url.search;
  var search_params = new URLSearchParams(query_string);

  search_params.set('ano_letivo', $j(this).val());
  url.search = search_params.toString();

  window.location.href = url.toString();
});

function cloneValues(componente_id, classe){
  const valor = $j('#' + classe + '_' + componente_id).val();

  $j('.' + classe).each(function() {
    const id = $j(this).data('id')

    if ($j('.check_' + id).is(':checked')) {
      $j(this).val(valor);
      if (classe === 'anos_letivos') {
        $j(this).trigger('chosen:updated');
      }
    }
  }, this);
}

$j('#disciplinas').on('change','input[name^="disciplinas"]',function () {
  $j(this).closest('div').find('input[name^="usar_componente"]').prop('checked', $j(this).is(':checked'));
})

$j(document).ready(function() {
    $j(document).on('keyup change', 'input.aulas_semana', function() {
        var oldValue = this.value;

        this.value = this.value.replace(/[^0-9\.]/g, '');
        this.value = this.value.replace('.', '');

        if (oldValue != this.value)
            messageUtils.error('Informe apenas números.', this);
    });
});
