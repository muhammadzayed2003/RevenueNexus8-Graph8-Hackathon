<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TimeMachineController extends Controller
{
    public function index(): View
    {
        return view('modules.time-machine.index');
    }
}