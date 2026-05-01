<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function showPointshop()
    {
        return view('pages.point-shop');
    }
}
