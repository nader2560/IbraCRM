<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacebookController extends Controller
{
    public function index(){
        return view('admin.feedback.fbm');
    }
}
