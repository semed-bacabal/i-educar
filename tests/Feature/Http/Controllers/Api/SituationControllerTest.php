<?php

namespace Tests\Feature\Http\Controllers\Api;

use Database\Factories\LegacyUserFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpenApiGenerator\Attributes\Controller;
use OpenApiGenerator\Attributes\GET;
use OpenApiGenerator\Attributes\Response;
use OpenApiGenerator\Types\SchemaType;
use Tests\TestCase;

#[Controller]
class SituationControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(LegacyUserFactory::new()->admin()->create());
    }

    #[
        GET('/api/situation', ['Situation'], 'Get all situations'),
        Response(200, schemaType: SchemaType::ARRAY, ref: 'Situation')
    ]
    public function test_index(): void
    {
        $response = $this->get('api/situation');
        $expected = [
            'data' => [
                1 => 'Aprovado',
                2 => 'Reprovado',
                3 => 'Cursando',
                4 => 'Transferido',
                5 => 'Reclassificado',
                6 => 'Deixou de Frequentar',
                9 => 'Exceto Transferidos/Deixou de Frequentar',
                10 => 'Todas',
                12 => 'Aprovado com dependência',
                13 => 'Aprovado pelo conselho',
                14 => 'Reprovado por faltas',
                15 => 'Falecido',
            ],
        ];
        $response->assertOk();
        $response->assertJson($expected);
    }
}
