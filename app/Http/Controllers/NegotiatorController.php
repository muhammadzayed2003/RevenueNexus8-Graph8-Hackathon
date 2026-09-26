<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class NegotiatorController extends Controller
{
    public function index(): View
    {
        return view('modules.negotiator.index');
    }
}