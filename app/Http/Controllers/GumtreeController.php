<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GumtreeController extends Controller
{
    public function index(){
        return view('admin.feedback.gumtree');
    }
}
