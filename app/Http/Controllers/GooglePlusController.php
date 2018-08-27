<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GooglePlusController extends Controller
{
    public function index(){
        return view('admin.feedback.gplus');
    }
}
