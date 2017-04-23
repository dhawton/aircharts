<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model {
    protected $table = 'positions';
    public $timestamps = true;
    protected $connection = "vattrack";
}