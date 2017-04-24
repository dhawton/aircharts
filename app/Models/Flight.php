<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Classes\MathHelper;

class Flight extends Model {
    protected $table = 'flights';
    public $timestamps = true;
    protected $dates = ['departure_time','arrival_time','deleted_at'];
    protected $connection = "vattrack";

    function checkArrival() {
        if ($this->arrival == "ZZZZ") {
            return -1;
        }
        $arrap = Airport::find($this->arrival);
        if (!$arrap) return -1;

        if (MathHelper::calc_distance($this->lat, $this->lon, $arrap->lat, $arrap->lon) < 15 && $this->altitude < $arrap->elevation + 2000) {
            return true;
        }
        return false;
    }

    function checkDeparture() {
        if ($this->departure == "ZZZZ") return -1;

        $depap = Airport::find($this->departure);
        if (!$depap) return -1;

        if (MathHelper::calc_distance($this->lat, $this->lon, $depap->lat, $depap->lon) < 5 && $this->altitude > $depap->elevation + 2000)
            return true;

        return false;
    }

    function airborne() {
        if (!$this->checkDeparture() && !$this->checkArrival()) {
            if ($this->alt > 2000 && $this->spd > 40) return true;
        }
        return false;
    }

    function positions() {
        return $this->hasMany('App\Models\Positions', 'id', 'flight_id');
    }
}