<?php

namespace App\Models\Exporter\Builders;

use App\Models\Exporter\Employee;
use App\Support\Database\JoinableBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

/**
 * @extends Builder<Employee>
 */
class EmployeeEloquentBuilder extends Builder
{
    use JoinableBuilder;

    /**
     * @param array $columns
     * @return TeacherEloquentBuilder
     *
     * @phpstan-return \Illuminate\Database\Query\Builder
     */
    public function person($columns)
    {
        $this->addSelect(
            $this->joinColumns('person', $columns)
        );

        return $this->join('exporter_person as person', function (JoinClause $join) {
            $join->on('exporter_employee.id', '=', 'person.id');
        });
    }

    /**
     * @return TeacherEloquentBuilder
     *
     * @phpstan-return \Illuminate\Database\Query\Builder
     */
    public function disabilities()
    {
        $this->addSelect(
            $this->joinColumns('disabilities', ['disabilities'])
        );

        return $this->leftJoin('exporter_disabilities as disabilities', function (JoinClause $join) {
            $join->on('exporter_employee.id', '=', 'disabilities.person_id');
        });
    }

    /**
     * @return TeacherEloquentBuilder
     *
     * @phpstan-return \Illuminate\Database\Query\Builder
     */
    public function phones()
    {
        $this->addSelect(
            $this->joinColumns('phones', ['phones'])
        );

        return $this->leftJoin('exporter_phones as phones', function (JoinClause $join) {
            $join->on('exporter_employee.id', '=', 'phones.person_id');
        });
    }

    /**
     * @param array $columns
     * @return TeacherEloquentBuilder
     *
     * @phpstan-return \Illuminate\Database\Query\Builder
     */
    public function place($columns)
    {
        $this->addSelect(
            $this->joinColumns('place', $columns)
        );

        return $this->leftJoin('person_has_place', function (JoinClause $join) {
            $join->on('exporter_employee.id', '=', 'person_has_place.person_id');
        })->leftJoin('addresses as place', function (JoinClause $join) {
            $join->on('person_has_place.place_id', '=', 'place.id');
        });
    }
}
