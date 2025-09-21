<?php

namespace iEducar\Packages\Educacenso\Services\Version2019;

use App\Models\Educacenso\Registro40;
use App\Models\Educacenso\RegistroEducacenso;
use App\Models\Employee;
use App\Models\EmployeeInep;
use App\Models\LegacyInstitution;
use App\Models\LegacySchool;
use App\Models\SchoolInep;
use App\Models\SchoolManager;
use App\User;
use iEducar\Packages\Educacenso\Services\RegistroImportInterface;
use iEducar\Packages\Educacenso\Services\Version2019\Models\Registro40Model;

class Registro40Import implements RegistroImportInterface
{
    /**
     * @var Registro40
     */
    protected $model;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var LegacyInstitution
     */
    protected $institution;

    /**
     * Faz a importação dos dados a partir da linha do arquivo
     *
     * @param int                $year
     * @return void
     */
    public function import(RegistroEducacenso $model, $year, $user): void
    {
        $this->user = $user;
        $this->model = $model;
        $this->institution = app(LegacyInstitution::class);

        $employee = $this->getEmployee();
        if (empty($employee)) {
            return;
        }

        $this->createOrUpdateManager($employee);
    }

    /**
     * @return Registro40|RegistroEducacenso
     */
    public static function getModel($arrayColumns)
    {
        $registro = new Registro40Model();
        $registro->hydrateModel($arrayColumns);

        return $registro;
    }

    private function getEmployee(): ?Employee
    {
        $inepNumber = $this->model->inepGestor;
        $employeeInep = EmployeeInep::where('cod_docente_inep', $inepNumber)->first();

        if (empty($employeeInep)) {
            return null;
        }

        return $employeeInep->employee ?? null;
    }

    private function createOrUpdateManager(Employee $employee): void
    {
        $school = $this->getSchool();

        if (empty($school)) {
            return;
        }

        $manager = SchoolManager::firstOrNew([
            'employee_id' => $employee->id,
            'school_id' => $school->id,
        ]);

        $manager->role_id = $this->model->cargo;
        $manager->access_criteria_id = $this->model->criterioAcesso ?: null;
        $manager->access_criteria_description = $this->model->especificacaoCriterioAcesso;
        $manager->link_type_id = (int) $this->model->tipoVinculo ?: null;
        if (! $this->existsChiefSchoolManager($school)) {
            $manager->chief = true;
        }

        $manager->saveOrFail();
    }

    protected function existsChiefSchoolManager(LegacySchool $school): bool
    {
        return $school->schoolManagers()->where('chief', true)->exists();
    }

    /**
     * @return LegacySchool
     */
    protected function getSchool(): ?LegacySchool
    {
        $schoolInep = SchoolInep::where('cod_escola_inep', $this->model->inepEscola)->first();
        if ($schoolInep) {
            return $schoolInep->school;
        }

        return null;
    }
}
