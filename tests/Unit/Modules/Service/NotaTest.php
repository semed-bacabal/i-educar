<?php

class Avaliacao_Service_NotaTest extends Avaliacao_Service_TestCommon
{
    public function test_instancia_de_nota_componente_e_registrada_apenas_uma_vez_no_boletiom()
    {
        $service = $this->_getServiceInstance();

        $nota = new Avaliacao_Model_NotaComponente([
            'componenteCurricular' => 1,
            'nota' => '5.1',
        ]);

        // Atribuição simples
        $service->addNota($nota)
            ->addNota($nota);

        $this->assertEquals(1, count($service->getNotas()));

        // Via atribuição em lote
        $nota = clone $nota;
        $service->addNotas([$nota, $nota, $nota]);

        $this->assertEquals(2, count($service->getNotas()));
    }

    public function test_adiciona_nota_no_boletim()
    {
        $this->markTestSkipped();
        $service = $this->_getServiceInstance();

        $nota = new Avaliacao_Model_NotaComponente([
            'componenteCurricular' => 1,
            'nota' => 5.72,
        ]);

        $notaOriginal = clone $nota;
        $service->addNota($nota);

        $notas = $service->getNotas();
        $serviceNota = array_shift($notas);

        // Valores declarados explicitamente, verificação explícita
        $this->assertEquals($notaOriginal->nota, $serviceNota->nota);
        $this->assertEquals($notaOriginal->get('componenteCurricular'), $serviceNota->get('componenteCurricular'));

        // Valores populados pelo service
        $this->assertNotEquals($notaOriginal->etapa, $serviceNota->etapa);
        $this->assertEquals(1, $serviceNota->etapa);
        $this->assertEquals(5, $serviceNota->notaArredondada);

        // Validadores injetados no objeto
        $validators = $serviceNota->getValidatorCollection();
        $this->assertInstanceOf('CoreExt_Validate_Choice', $validators['componenteCurricular']);
        $this->assertInstanceOf('CoreExt_Validate_Choice', $validators['etapa']);

        // Opções dos validadores

        // Componentes curriculares existentes para o aluno
        $this->assertEquals(
            array_keys($this->_getConfigOptions('componenteCurricular')),
            array_values($validators['componenteCurricular']->getOption('choices'))
        );

        // Etapas possíveis para o lançamento de nota
        $this->assertEquals(
            array_merge(range(1, count($this->_getConfigOptions('anoLetivoModulo'))), ['Rc']),
            $validators['etapa']->getOption('choices')
        );
    }

    /**
     * Testa o service adicionando notas de apenas um componente curricular,
     * para todas as etapas regulares (1 a 4).
     */
    public function test_salvar_notas_de_um_componente_curricular_no_boletim()
    {
        $this->markTestSkipped();
        $notaAluno = $this->_getConfigOption('notaAluno', 'instance');

        $notas = [
            new Avaliacao_Model_NotaComponente([
                'componenteCurricular' => 1,
                'nota' => 7.25,
                'etapa' => 1,
            ]),
            new Avaliacao_Model_NotaComponente([
                'componenteCurricular' => 1,
                'nota' => 9.25,
                'etapa' => 2,
            ]),
            new Avaliacao_Model_NotaComponente([
                'componenteCurricular' => 1,
                'nota' => 8,
                'etapa' => 3,
            ]),
            new Avaliacao_Model_NotaComponente([
                'componenteCurricular' => 1,
                'nota' => 8.5,
                'etapa' => 4,
            ]),
        ];

        $media = new Avaliacao_Model_NotaComponenteMedia([
            'notaAluno' => $notaAluno->id,
            'componenteCurricular' => 1,
            'media' => 8.25,
            'mediaArredondada' => 8,
            'etapa' => 4,
        ]);

        $media->markOld();

        // Configura mock para Avaliacao_Model_NotaComponenteDataMapper
        $mock = $this->getCleanMock('Avaliacao_Model_NotaComponenteDataMapper');

        $mock->expects($this->at(0))
            ->method('findAll')
            ->with([], ['notaAluno' => $notaAluno->id], ['etapa' => 'ASC'])
            ->willReturn([]);

        $mock->expects($this->at(1))
            ->method('save')
            ->with($notas[0])
            ->willReturn(true);

        $mock->expects($this->at(2))
            ->method('save')
            ->with($notas[1])
            ->willReturn(true);

        $mock->expects($this->at(3))
            ->method('save')
            ->with($notas[2])
            ->willReturn(true);

        $mock->expects($this->at(4))
            ->method('save')
            ->with($notas[3])
            ->willReturn(true);

        $mock->expects($this->at(5))
            ->method('findAll')
            ->with([], ['notaAluno' => $notaAluno->id], ['etapa' => 'ASC'])
            ->willReturn($notas);

        $this->_setNotaComponenteDataMapperMock($mock);

        // Configura mock para Avaliacao_Model_NotaComponenteMediaDataMapper
        $mock = $this->getCleanMock('Avaliacao_Model_NotaComponenteMediaDataMapper');

        $mock->expects($this->at(0))
            ->method('findAll')
            ->with([], ['notaAluno' => $notaAluno->id])
            ->willReturn([]);

        $mock->expects($this->at(1))
            ->method('find')
            ->with([$notaAluno->id, $this->_getConfigOption('matricula', 'cod_matricula')])
            ->willReturn(null);

        $mock->expects($this->at(2))
            ->method('save')
            ->with($media)
            ->willReturn(true);

        $this->_setNotaComponenteMediaDataMapperMock($mock);

        $service = $this->_getServiceInstance();

        $service->addNotas($notas);
        $service->saveNotas();
    }

    /**
     * Testa o service adicionando novas notas para um componente curricular,
     * que inclusive já tem a nota lançada para a segunda etapa.
     */
    public function test_salvas_notas_de_um_componente_com_etapas_lancadas()
    {
        $this->markTestSkipped();
        $notaAluno = $this->_getConfigOption('notaAluno', 'instance');

        $notas = [
            new Avaliacao_Model_NotaComponente([
                'componenteCurricular' => 1,
                'nota' => 7.25,
                'etapa' => 2,
            ]),
            new Avaliacao_Model_NotaComponente([
                'componenteCurricular' => 1,
                'nota' => 9.25,
                'etapa' => 3,
            ]),
        ];

        $notasPersistidas = [
            new Avaliacao_Model_NotaComponente([
                'id' => 1,
                'notaAluno' => $notaAluno->id,
                'componenteCurricular' => 1,
                'nota' => 8.25,
                'notaArredondada' => 8,
                'etapa' => 1,
            ]),
            new Avaliacao_Model_NotaComponente([
                'id' => 2,
                'notaAluno' => $notaAluno->id,
                'componenteCurricular' => 1,
                'nota' => 9.5,
                'notaArredondada' => 9,
                'etapa' => 2,
            ]),
        ];

        $mediasPersistidas = [
            new Avaliacao_Model_NotaComponenteMedia([
                'notaAluno' => $notaAluno->id,
                'componenteCurricular' => 1,
                'media' => 4.4375,
                'mediaArredondada' => 4,
                'etapa' => 2,
            ]),
        ];

        $mediasPersistidas[0]->markOld();

        // Configura mock para Avaliacao_Model_NotaComponenteDataMapper
        $mock = $this->getCleanMock('Avaliacao_Model_NotaComponenteDataMapper');

        $mock->expects($this->at(0))
            ->method('findAll')
            ->with([], ['notaAluno' => $notaAluno->id], ['etapa' => 'ASC'])
            ->willReturn($notasPersistidas);

        $mock->expects($this->at(1))
            ->method('save')
            ->with($notas[0])
            ->willReturn(true);

        $mock->expects($this->at(2))
            ->method('save')
            ->with($notas[1])
            ->willReturn(true);

        $mock->expects($this->at(3))
            ->method('findAll')
            ->with([], ['notaAluno' => $notaAluno->id], ['etapa' => 'ASC'])
            ->willReturn([$notasPersistidas[0], $notas[0], $notas[1]]);

        $this->_setNotaComponenteDataMapperMock($mock);

        // Configura mock para Avaliacao_Model_NotaComponenteMediaDataMapper
        $mock = $this->getCleanMock('Avaliacao_Model_NotaComponenteMediaDataMapper');

        $mock->expects($this->at(0))
            ->method('findAll')
            ->with([], ['notaAluno' => $notaAluno->id])
            ->willReturn($mediasPersistidas);

        $mock->expects($this->at(1))
            ->method('find')
            ->with([$notaAluno->id, $this->_getConfigOption('matricula', 'cod_matricula')])
            ->willReturn($mediasPersistidas[0]);

        // Valores de média esperados
        $media = clone $mediasPersistidas[0];
        $media->etapa = 3;
        $media->media = 6.1875;
        $media->mediaArredondada = 6;

        $mock->expects($this->at(2))
            ->method('save')
            ->with($media)
            ->willReturn(true);

        $this->_setNotaComponenteMediaDataMapperMock($mock);

        $service = $this->_getServiceInstance();
        $service->addNotas($notas);
        $service->saveNotas();
    }
}
