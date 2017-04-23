<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Positions extends Model {
    protected $table = 'positions';
    public $timestamps = true;
    protected $connection = "vattrack";
}