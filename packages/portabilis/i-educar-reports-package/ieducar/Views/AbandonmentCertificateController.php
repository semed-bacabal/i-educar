<?php

use App\Menu;

class AbandonmentCertificateController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999806;

    /**
     * @var string
     */
    protected $_titulo = 'Atestado de abandono';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb($this->titulo(), [
            url('intranet/educar_index.php') => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic(['escolaObrigatorioParaNivelEscolar'], ['required' => true]);
        $this->inputsHelper()->simpleSearchMatricula(null, ['required' => true]);
        $this->campoMemo('observacao', 'Observação', $this->observacao, 48, 5, false);
        $this->inputsHelper()->checkbox('emitir_nome_diretor', ['label' => 'Emitir assinatura do gestor escolar']);
        $this->inputsHelper()->checkbox('emitir_secretario_escolar', ['label' => 'Emitir assinatura do secretário escolar']);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('matricula', (int) $this->getRequest()->matricula_id);
        $this->report->addArg('observacao', $this->getRequest()->observacao);
        $this->report->addArg('emitir_nome_diretor', (bool) $this->getRequest()->emitir_nome_diretor);
        $this->report->addArg('emitir_secretario_escolar', (bool) $this->getRequest()->emitir_secretario_escolar);

        $this->report->addArg('assinatura_secretario', _cl('report.termo_assinatura_secretario'));
        $this->report->addArg('assinatura_diretor', _cl('report.termo_assinatura_diretor'));
    }

    /**
     * @return AbandonmentCertificateReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new AbandonmentCertificateReport();
    }

    /**
     * @return string
     */
    public function titulo()
    {
        $menu = Menu::query()->where('process', $this->_processoAp)->first();

        return $menu->title;
    }
}
