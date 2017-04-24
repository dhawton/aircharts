<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ACController extends Controller
{
    public function getIndex() {
        $mappoints = \Storage::disk('local')->get('airport.cache');

        return view('index', ['mappoints' => $mappoints]);
    }
}
