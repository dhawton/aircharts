<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ECRController extends Controller
{
    public function getIndex() {
        return view('ECR.index');
    }
}
