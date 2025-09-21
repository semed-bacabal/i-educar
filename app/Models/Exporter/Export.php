<?php

namespace App\Models\Exporter;

use App\Exports\EloquentExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property array<int, string> $fillable
 * @property Model $model
 * @property array<int, string>  $fields
 * @property array<int, array>  $filters
 */
class Export extends Model
{
    protected $table = 'export';

    protected $fillable = [
        'user_id',
        'model',
        'fields',
        'url',
        'hash',
        'filename',
        'filters',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'fields' => 'json',
        'filters' => 'json',
    ];

    /**
     * @return array<int, Model>
     */
    public function getAllowedExports()
    {
        return [
            1 => new Enrollment,
            2 => new Student,
            3 => new Teacher,
            4 => new SocialAssistance,
            5 => new Stage,
            6 => new Employee,
        ];
    }

    /**
     * @param int $code
     * @return mixed
     */
    public function getExportByCode($code)
    {
        return $this->getAllowedExports()[$code] ?? new Student;
    }

    /**
     * @return EloquentExporter
     */
    public function getExporter()
    {
        return new EloquentExporter($this);
    }

    /**
     * @return Model
     */
    public function newExportModel()
    {
        $model = $this->model;

        return new $model;
    }

    /**
     * @return Builder<Model>
     */
    public function newExportQueryBuilder()
    {
        return $this->newExportModel()->newQuery();
    }

    /**
     * @return Builder<Model>
     */
    public function getExportQuery()
    {
        $select = [];
        $relations = [];

        foreach ($this->fields as $field) {
            if (!Str::contains($field, '.')) {
                $select[] = $field;

                continue;
            }

            [$relation, $column] = explode('.', $field);

            $relations[$relation][] = $column;
        }

        $query = $this->newExportQueryBuilder()->select($select);

        foreach ($relations as $relation => $columns) {
            $query->{$relation}($columns);
        }

        /** @var Builder<Model> $query */
        $this->applyFilters($query);

        return $query;
    }

    /**
     * @param Builder<Model> $query
     */
    public function applyFilters(Builder $query): void
    {
        foreach ($this->filters as $filter) {
            $column = $filter['column'];
            $operator = $filter['operator'];
            $value = $filter['value'];

            switch ($operator) {
                case '=':
                    $query->whereRaw("{$column} {$operator} {$value}");

                    break;

                case 'in':
                    $value = implode(', ', $value);
                    $query->whereRaw("{$column} {$operator} ({$value})");

                    break;
                case '@>':
                    if (is_array($value)) {
                        $query->where(function ($q) use ($column, $value) {
                            foreach ($value as $v) {
                                $q->orWhereRaw("{$column} @> ('{{$v}}')");
                            }
                        });
                    } else {
                        $query->whereRaw("{$column} @> ('{{$value}}')");
                    }

                    break;
            }
        }
    }
}
