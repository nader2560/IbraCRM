<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AmazonController extends Controller
{
    public function index(){
        return view('admin.feedback.amazon');
    }
}
