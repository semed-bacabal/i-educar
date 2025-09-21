<?php

namespace iEducar\Packages\Educacenso\Services\Version2020;

use App\Models\Educacenso\Registro30;
use App\Models\Educacenso\RegistroEducacenso;
use App\Models\LegacyInstitution;
use App\Models\LegacyStudent;
use iEducar\Packages\Educacenso\Services\Version2019\Registro30Import as Registro30Import2019;
use iEducar\Packages\Educacenso\Services\Version2020\Models\Registro30Model;

class Registro30Import extends Registro30Import2019
{
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

        $person = $this->getOrCreatePerson();

        $this->createRace($person);
        $this->createDeficiencies($person);

        if ($this->model->isStudent()) {
            $student = $this->getOrCreateStudent($person);
            $this->storeStudentData($student);
        }

        if ($this->model->isTeacher() || $this->model->isManager()) {
            $employee = $this->getOrCreateEmployee($person);
            $this->storeEmployeeData($employee);
        }
    }

    private function storeStudentData(LegacyStudent $student): void
    {
        $this->createStudentInep($student);
        $this->createRecursosProvaInep($student);
        $this->createCertidaoNascimento($student);

        $student->save();
    }

    /**
     * @return Registro30|RegistroEducacenso
     */
    public static function getModel($arrayColumns)
    {
        $registro = new Registro30Model();
        $registro->hydrateModel($arrayColumns);

        return $registro;
    }
}
