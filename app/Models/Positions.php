<?php

namespace App\Models;

class Positions extends Eloquent {
    protected $table = 'positions';
    public $timestamps = true;
    protected $connection = "vattrack";
}