<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        // Load the view with layout
        return view('pages/dashboard');
    }
    
    // You can also pass data to your view
    public function show($page = 'dashboard')
    {
        // $data = [
        //     'title' => ucfirst($page),
        //     'analytics_data' => [
        //         'accuracy' => '98.7%',
        //         'precision' => '1.2%',
        //         'linearity' => '0.999'
        //     ]
        // ];
        
        return view('pages/' . $page, $data);
    }
}