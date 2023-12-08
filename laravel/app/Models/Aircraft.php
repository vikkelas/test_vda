<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aircraft extends Model
{
    use HasFactory;
    protected $table = 'aircrafts';
    public function flightsFilter($date_from, $date_to): HasMany
    {
        return $this->hasMany(Flight::class, 'aircraft_id')
            ->where([
                ['takeoff', '>=' ,$date_from],
                ['takeoff', '<=',$date_to],
            ])
            ->orderBy('takeoff');
    }

    public function flightsRelativeTakeoff($airport, $takeoffDate): Model|HasMany|null
    {
        return $this->hasMany(Flight::class, 'aircraft_id')
            ->where([
                ['airport_id2', $airport],
                ['landing', '<=', $takeoffDate]
            ])
            ->orderBy('landing', 'desc');
    }

    public function flightsRelativeLanding($airport, $landingDate): Model|HasMany|null
    {
        return $this->hasMany(Flight::class, 'aircraft_id')
            ->where([
                ['airport_id1', $airport],
                ['landing', '>=', $landingDate]
            ])
            ->orderBy('landing', 'asc');
    }



}
