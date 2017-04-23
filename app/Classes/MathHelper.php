<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 4/22/2017
 * Time: 6:35 PM
 */

namespace App\Classes;


class MathHelper
{
    public static function calc_distance($lat1, $lon1, $lat2, $lon2) {
        $lat1 = deg2rad(floatval($lat1)); $lon1 = deg2rad(floatval($lon1));
        $lat2 = deg2rad(floatval($lat2)); $lon2 = deg2rad(floatval($lon2));

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        return ($dist * 60 * 1.1515 * 0.8684);
    }
}