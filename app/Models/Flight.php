<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Classes\MathHelper;

class Flight extends Model {
    protected $table = 'flights';
    public $timestamps = true;
    protected $dates = ['departure_time','arrival_time','deleted_at'];
    protected $connection = "vattrack";

    function checkArrival() {
        if ($this->arrival == "ZZZZ") {
            return false;
        }
        $arrap = Airport::find($this->arrival);
        if (!$arrap) return false;

        if (MathHelper::calc_distance($this->lat, $this->lon, $arrap->lat, $arrap->lon) < 3 && $this->alt < $arrap->elevation + 500) {
            return true;
        }
        return false;
    }

    function checkDeparture() {
        if ($this->departure == "ZZZZ") return -1;

        $depap = Airport::find($this->departure);
        if (!$depap) return -1;

        if (MathHelper::calc_distance($this->lat, $this->lon, $depap->lat, $depap->lon) < 3 && $this->alt < $depap->elevation + 500)
            return true;

        return false;
    }

    function arrivalEst() {
        if ($this->status != "En-Route") { return 0; }
        if (!$this->arrival || $this->spd <= 0) { return; }
        $arrap = Airport::find($this->arrival);
        if (!$arrap) return;
        $dist = MathHelper::calc_distance($this->lat, $this->lon, $arrap->lat, $arrap->lon);
        $time = $dist / $this->spd;         // Ground speed estimate
        $hr = floor($time);
        $min = intval((($hr - $time) + .25) * 60);
        $this->arrival_est = Carbon::now("UTC")->addHour($hr)->addMinute($min)->format('Y-m-d H:i:s');;
        $this->save();
    }

    function airborne() {
        if ($this->spd > 50) {
            return true;
        }
        return false;
    }

    function positions() {
        return $this->hasMany('App\Models\Positions', 'id', 'flight_id');
    }
}
