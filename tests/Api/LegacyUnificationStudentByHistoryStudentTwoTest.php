<?php

namespace Tests\Api;

use App\Models\LegacyStudent;
use App\Models\LogUnification;
use App\Models\LogUnificationOldData;
use Database\Factories\LegacySchoolHistoryDisciplineFactory;
use Database\Factories\LegacySchoolHistoryFactory;
use Database\Factories\LegacyStudentFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\LoginFirstUser;
use Tests\TestCase;

class LegacyUnificationStudentByHistoryStudentTwoTest extends TestCase
{
    use DatabaseTransactions;
    use LoginFirstUser;

    private LegacyStudent $studentOne;

    private LegacyStudent $studentTwo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginWithFirstUser();

        $this->studentOne = LegacyStudentFactory::new()->create();
        $this->studentTwo = LegacyStudentFactory::new()->create();
    }

    public function test_unification_by_history_student_two(): void
    {
        $schoolHistory = LegacySchoolHistoryFactory::new()->create([
            'ref_cod_aluno' => $this->studentTwo->getKey(),
        ]);

        $schoolHistoryDiscipline = LegacySchoolHistoryDisciplineFactory::new()->create([
            'ref_ref_cod_aluno' => $schoolHistory->student->getKey(),
            'ref_sequencial' => $schoolHistory->sequencial,
        ]);

        $request = [
            'tipoacao' => 'Novo',
        ];

        $data = [
            'alunos' => collect([
                [
                    'codAluno' => $this->studentOne->getKey(),
                    'aluno_principal' => true,
                ],
                [
                    'codAluno' => $this->studentTwo->getKey(),
                    'aluno_principal' => false,
                ],
            ]),
        ];

        $payload = array_merge($request, $data);

        $this->post('/intranet/educar_unifica_aluno.php', $payload)
            ->assertRedirectContains(route('student-log-unification.index'));

        $log = LogUnification::query()
            ->where('main_id', $this->studentOne->getKey())
            ->where('type', 'App\Models\Student')
            ->where('active', true)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($log->duplicates_id, [$this->studentTwo->getKey()]);
        $this->assertTrue($log->created_at->isToday());
        $this->assertTrue($log->updated_at->isToday());

        $logOldDataSchoolHistory = LogUnificationOldData::query()
            ->where('unification_id', $log->getKey())
            ->where('table', $schoolHistory->getTable())
            ->get();

        $this->assertNotNull($logOldDataSchoolHistory);
        $this->assertCount(1, $logOldDataSchoolHistory);
        $logOldDataSchoolHistory = $logOldDataSchoolHistory->first();
        $this->assertEquals($logOldDataSchoolHistory->keys[0], [
            'id' => $schoolHistory->getKey(),
        ]);

        $logOldDataStudent = LogUnificationOldData::query()
            ->where('unification_id', $log->getKey())
            ->where('table', $this->studentTwo->getTable())
            ->get();

        $this->assertNotNull($logOldDataStudent);
        $this->assertCount(1, $logOldDataStudent);
        $logOldDataStudent = $logOldDataStudent->first();
        $this->assertEquals($logOldDataStudent->keys[0], [
            'cod_aluno' => $this->studentTwo->getKey(),
        ]);

        $this->assertDatabaseHas($log, [
            'id' => $log->getKey(),
            'type' => 'App\Models\Student',
            'main_id' => $this->studentOne->getKey(),
            'active' => true,
        ])->assertDatabaseHas($this->studentOne, [
            'cod_aluno' => $this->studentOne->getKey(),
            'ativo' => 1,
        ])->assertDatabaseHas($this->studentTwo, [
            'cod_aluno' => $this->studentTwo->getKey(),
            'ativo' => 0,
        ])->assertDatabaseHas($schoolHistory, [
            'id' => $schoolHistory->getKey(),
            'ref_cod_aluno' => $this->studentOne->getKey(),
            'sequencial' => 1,
        ])->assertDatabaseHas($schoolHistoryDiscipline, [
            'id' => $schoolHistoryDiscipline->getKey(),
            'ref_ref_cod_aluno' => $this->studentOne->getKey(),
            'ref_sequencial' => 1,
        ]);
    }
}
