<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $amz1 = new \Sonnenglas\AmazonMws\AmazonReportRequest('store1');
        $amz1->setReportType('_GET_MERCHANT_LISTINGS_ALL_DATA_');
        $amz1->requestReport();
        return view('admin.dashboard.index');
    }
}
