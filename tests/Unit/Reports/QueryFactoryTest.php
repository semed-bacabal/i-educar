<?php

namespace Tests\Unit\Reports;

use iEducar\Modules\Reports\QueryFactory\QueryFactory;
use Tests\TestCase;

class QueryFactoryTest extends TestCase
{
    private static $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$pdo) {
            self::$pdo = $this->getConnection()->getPdo();
            self::tearDownAfterClass();
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::$pdo->exec(
            "DELETE FROM pmieducar.usuario WHERE cod_usuario IN (-1,-2);\n" .
            'SET session_replication_role = DEFAULT;'
        );
    }

    public function test_unvalued_key()
    {
        $fakeClass = new class(self::$pdo, []) extends QueryFactory
        {
            protected $keys = ['fake_key'];
        };

        $this->expectException(\InvalidArgumentException::class);
        $fakeClass->getData();
    }

    public function test_array_value()
    {
        self::$pdo->exec(
            "SET session_replication_role = replica;\n" .
            'INSERT INTO pmieducar.usuario (cod_usuario, ref_cod_instituicao, ref_funcionario_cad, data_cadastro, ativo) VALUES (-1, 1, 1, NOW(), 1), (-2, 1, 1, NOW(), 1);'
        );

        $fakeClass = new class(self::$pdo, []) extends QueryFactory
        {
            protected $keys = ['usuarios'];

            protected $query = 'SELECT * FROM pmieducar.usuario WHERE cod_usuario IN (:usuarios)';
        };
        $fakeClass->setParams(['usuarios' => [-1, -2]]);
        $data = $fakeClass->getData();
        $this->assertCount(2, $data);
    }
}
