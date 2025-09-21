<?php

namespace iEducar\Packages\Educacenso\Services\Version2019;

use App\Models\Educacenso\Registro50;
use App\Models\Educacenso\RegistroEducacenso;
use App\Models\Employee;
use App\Models\EmployeeInep;
use App\Models\LegacyDiscipline;
use App\Models\LegacyEmployeeRole;
use App\Models\LegacyInstitution;
use App\Models\LegacyRole;
use App\Models\LegacySchoolClass;
use App\Models\LegacySchoolClassTeacher;
use App\Models\LegacySchoolClassTeacherDiscipline;
use App\Models\SchoolClassInep;
use App\User;
use iEducar\Packages\Educacenso\Services\RegistroImportInterface;
use iEducar\Packages\Educacenso\Services\Version2019\Models\Registro50Model;

class Registro50Import implements RegistroImportInterface
{
    /**
     * @var Registro50
     */
    private $model;

    /**
     * @var User
     */
    private $user;

    /**
     * @var int
     */
    private $year;

    /**
     * @var LegacyInstitution
     */
    private $institution;

    /**
     * @var LegacyRole
     */
    private $_legacyRole;

    /**
     * Faz a importação dos dados a partir da linha do arquivo
     *
     * @param int                $year
     * @return void
     */
    public function import(RegistroEducacenso $model, $year, $user): void
    {
        $this->year = $year;
        $this->user = $user;
        $this->model = $model;
        $this->institution = app(LegacyInstitution::class);

        $schoolClass = $this->getSchoolClass();
        if (! $schoolClass) {
            return;
        }

        $employee = $this->getEmployee();
        if (empty($employee)) {
            return;
        }

        $this->setEmployeeAsTeacher($employee);
        $this->createSchoolClassTeacher($schoolClass, $employee);
    }

    /**
     * @return Registro50|RegistroEducacenso
     */
    public static function getModel($arrayColumns)
    {
        $registro = new Registro50Model();
        $registro->hydrateModel($arrayColumns);

        return $registro;
    }

    /**
     * @return LegacySchoolClass
     */
    protected function getSchoolClass(): ?LegacySchoolClass
    {
        if (empty($this->model->inepTurma)) {
            return null;
        }

        return SchoolClassInep::where('cod_turma_inep', $this->model->inepTurma)->first()->schoolClass ?? null;
    }

    protected function getEmployee(): ?Employee
    {
        $inepNumber = $this->model->inepDocente;
        if (! $inepNumber) {
            return null;
        }

        $employeeInep = EmployeeInep::where('cod_docente_inep', $inepNumber)->first();
        if (empty($employeeInep)) {
            return null;
        }

        return $employeeInep->employee ?? null;
    }

    /**
     * @param $employee Employee
     */
    private function setEmployeeAsTeacher(Employee $employee): void
    {
        if ($this->employeeHasTeacherRole($employee)) {
            return;
        }

        $defaultRole = $this->getDefaultTeacherRole();
        LegacyEmployeeRole::create([
            'ref_cod_funcao' => $defaultRole->id,
            'ref_cod_servidor' => $employee->id,
            'ref_ref_cod_instituicao' => $this->institution->id,
        ]);
    }

    /**
     * @param $employee Employee
     */
    private function employeeHasTeacherRole(Employee $employee): bool
    {
        return LegacyEmployeeRole::where('ref_cod_servidor', $employee->id)
            ->whereHas('role', function ($query): void {
                $query->ativo();
                $query->professor();
            })->exists();
    }

    private function getDefaultTeacherRole(): LegacyRole
    {
        if (! empty($this->_legacyRole)) {
            return $this->_legacyRole;
        }

        $this->_legacyRole = LegacyRole::firstOrCreate(
            [
                'ref_cod_instituicao' => $this->institution->id,
                'professor' => 1,
                'ativo' => 1,
            ],
            [
                'ref_usuario_cad' => $this->user->id,
                'nm_funcao' => 'Professor',
                'abreviatura' => 'Prof',
            ]
        );

        return $this->_legacyRole;
    }

    /**
     * @param $schoolClass LegacySchoolClass
     * @param $employee    Employee
     */
    private function createSchoolClassTeacher(LegacySchoolClass $schoolClass, Employee $employee): void
    {
        $schoolClassTeacher = LegacySchoolClassTeacher::firstOrNew([
            'ano' => $this->year,
            'instituicao_id' => $this->institution->id,
            'turma_id' => $schoolClass->id,
            'servidor_id' => $employee->id,
        ]);
        $schoolClassTeacher->funcao_exercida = $this->model->funcaoDocente;
        $schoolClassTeacher->tipo_vinculo = $this->model->tipoVinculo;
        $schoolClassTeacher->saveOrFail();

        $this->linkDisciplines($schoolClassTeacher);
    }

    /**
     * @param $schoolClassTeacher LegacySchoolClassTeacher
     * @param $employee           Employee
     */
    private function linkDisciplines(LegacySchoolClassTeacher $schoolClassTeacher): void
    {
        foreach ($this->model->componentes as $codigoEducacenso) {
            if (empty(trim($codigoEducacenso))) {
                continue;
            }

            $discipline = LegacyDiscipline::where('codigo_educacenso', $codigoEducacenso)->first();

            if (! $discipline) {
                continue;
            }

            LegacySchoolClassTeacherDiscipline::firstOrCreate([
                'professor_turma_id' => $schoolClassTeacher->id,
                'componente_curricular_id' => $discipline->id,
            ]);
        }
    }
}
