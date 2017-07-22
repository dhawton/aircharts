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
        $time = $time * 60 * 60;            // Convert to seconds
        $time += 10 * 60;                   // Add 10 minutes est. for arrival
        $time = time() + $time;
        $this->arrival_est = date("Y-m-d H:i:s", $time);
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
