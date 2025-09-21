<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property Individual $individual
 * @property int $guardian_type
 * @property string $transportation_provider
 * @property string $transportation_vehicle_type
 */
class Student extends Model
{
    use SoftDeletes;

    /**
     * @return BelongsTo<Individual, $this>
     */
    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'individual_id');
    }

    /**
     * @return BelongsTo<Religion, $this>
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * @return BelongsTo<Individual, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'created_by', 'id');
    }

    /**
     * @return BelongsTo<Individual, $this>
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'deleted_by', 'id');
    }

    /**
     * @return MorphOne<LogUnification, $this>
     */
    public function unification(): MorphOne
    {
        return $this->morphOne(LogUnification::class, 'main', 'type', 'main_id');
    }

    protected function guardianType(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value) {
                    return $value;
                }

                if ($this->individual->father) {
                    return GuardianType::FATHER;
                }

                if ($this->individual->mother) {
                    return GuardianType::MOTHER;
                }

                if ($this->individual->guardian) {
                    return GuardianType::OTHER;
                }

                return null;
            },
        );
    }

    protected function guardianTypeDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => (new GuardianType)->getDescriptiveValues()[(int) $this->guardian_type]
        );
    }

    protected function transportationProviderDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => (new TransportationProvider)->getDescriptiveValues()[(int) $this->transportation_provider]
        );
    }

    protected function transportationVehicleTypeDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                $value = str_replace(['{', '}'], '', $this->transportation_vehicle_type);

                return (new TransportationVehicleType)->getDescriptiveValues()[(int) $value] ?? null;
            }
        );
    }
}
