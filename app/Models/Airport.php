<?php

namespace App\Models;

class Airport extends Eloquent {
    protected $table = 'airports';
    public $timestamps = true;
    protected $connection = "vattrack";
}