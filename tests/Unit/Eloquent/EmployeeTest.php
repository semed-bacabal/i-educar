<?php

namespace Tests\Unit\Eloquent;

use App\Models\Employee;
use App\Models\EmployeeAllocation;
use App\Models\EmployeeGraduation;
use App\Models\EmployeeInep;
use App\Models\LegacyCourse;
use App\Models\LegacyDiscipline;
use App\Models\LegacyEmployeeRole;
use App\Models\LegacyIndividual;
use App\Models\LegacyInstitution;
use App\Models\LegacyPerson;
use App\Models\LegacySchool;
use App\Models\LegacySchoolingDegree;
use Database\Factories\EmployeeAllocationFactory;
use Database\Factories\EmployeeFactory;
use Database\Factories\EmployeeInepFactory;
use Database\Factories\LegacyCourseFactory;
use Database\Factories\LegacyDisciplineFactory;
use Database\Factories\LegacyEmployeeRoleFactory;
use Database\Factories\LegacyIndividualFactory;
use Database\Factories\LegacySchoolFactory;
use Tests\EloquentTestCase;

class EmployeeTest extends EloquentTestCase
{
    protected $relations = [
        'person' => LegacyPerson::class,
        'employeeAllocations' => EmployeeAllocation::class,
        'employeeRoles' => LegacyEmployeeRole::class,
        'graduations' => EmployeeGraduation::class,
        'schoolingDegree' => LegacySchoolingDegree::class,
        'institution' => LegacyInstitution::class,
    ];

    /**
     * @return string
     */
    protected function getEloquentModelName()
    {
        return Employee::class;
    }

    public function test_relationship_inep()
    {
        $employee = EmployeeFactory::new()->create();
        $employee->inep = EmployeeInepFactory::new()->create([
            'cod_servidor' => $employee->id,
        ]);
        $this->assertInstanceOf(EmployeeInep::class, $employee->inep);
    }

    public function test_relationship_individual()
    {
        $employee = EmployeeFactory::new()->create();
        $employee->individual = LegacyIndividualFactory::new()->create([
            'idpes' => $employee->id,
        ]);
        $this->assertInstanceOf(LegacyIndividual::class, $employee->individual);
    }

    public function test_get_id_attribute()
    {
        $this->assertInstanceOf(Employee::class, $this->model);
        $this->assertIsInt($this->model->id);
        $this->assertEquals($this->model->cod_servidor, $this->model->id);
    }

    public function test_scope_professor()
    {
        try {
            Employee::query()
                ->professor()
                ->first();
            $this->assertTrue(true);
        } catch (\Exception $exception) {
            $this->fail('Exception thrown due to scope error');
        }
    }

    public function test_scope_last_year()
    {
        try {
            Employee::query()
                ->lastYear()
                ->first();

            $this->assertTrue(true);
        } catch (\Exception $exception) {
            $this->fail('Exception thrown due to scope error');
        }
    }

    public function test_scope_current_year()
    {
        try {
            Employee::query()
                ->currentYear()
                ->first();

            $this->assertTrue(true);
        } catch (\Exception $exception) {
            $this->fail('Exception thrown due to scope error');
        }
    }

    public function test_relationship_schools(): void
    {
        EmployeeAllocationFactory::new()->create([
            'ref_cod_servidor' => $this->model,
            'ref_cod_escola' => LegacySchoolFactory::new()->create(),
        ]);

        $this->assertCount(1, $this->model->schools);
        $this->assertInstanceOf(LegacySchool::class, $this->model->schools->first());
    }

    public function test_relationship_disciplines(): void
    {
        $employeeRole = LegacyEmployeeRoleFactory::new()->create();
        $course = LegacyCourseFactory::new()->create();

        $employee = EmployeeFactory::new()->hasAttached(LegacyDisciplineFactory::new(), [
            'ref_ref_cod_instituicao' => $this->model->institution_id,
            'ref_cod_curso' => $course->id,
            'ref_cod_funcao' => $employeeRole->id,
        ], 'disciplines')->create();

        $this->assertCount(1, $employee->disciplines);
        $this->assertInstanceOf(LegacyDiscipline::class, $employee->disciplines->first());
    }

    public function test_relationship_courses(): void
    {
        $employee = EmployeeFactory::new()->hasAttached(LegacyCourseFactory::new(), [
            'ref_ref_cod_instituicao' => $this->model->institution_id,
        ], 'courses')->create();

        $this->assertCount(1, $employee->courses);
        $this->assertInstanceOf(LegacyCourse::class, $employee->courses->first());
    }

    protected function getLegacyAttributes(): array
    {
        return [
            'id' => 'cod_servidor',
            'workload' => 'carga_horaria',
            'created_at' => 'data_cadastro',
            'institution_id' => 'ref_cod_instituicao',
        ];
    }
}
