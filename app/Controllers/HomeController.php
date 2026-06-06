<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Plan;

class HomeController extends Controller
{
    public function index()
    {
        $plans = Plan::all();
        return $this->view('home', [
            'title' => 'Bienvenue sur Investian',
            'plans' => $plans
        ]);
    }
}
