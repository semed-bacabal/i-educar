<?php

class AuditEvaluationsFaultsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999828;

    /**
     * @var string
     */
    protected $_titulo = 'Auditoria';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Emissão da auditoria de notas', [
            url('intranet/educar_index.php') => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma', 'etapa']);

        $this->inputsHelper()->dynamic('EscolaObrigatorioParaNivelEscolar', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->dynamic('etapa', ['required' => false]);
        $this->inputsHelper()->simpleSearchAluno(null, ['required' => false]);
        $this->inputsHelper()->dynamic(['dataInicial', 'dataFinal']);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('etapa', (int) $this->getRequest()->etapa);
        $this->report->addArg('aluno', (int) $this->getRequest()->aluno_id);
        $this->report->addArg('data_inicio', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_fim', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
    }

    /**
     * @return AuditEvaluationsFaultsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new AuditEvaluationsFaultsReport();
    }
}
