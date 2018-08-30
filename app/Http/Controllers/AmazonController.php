<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AmazonController extends Controller
{
    public function index(){

        set_time_limit(600);

//        $amz1 = new \Sonnenglas\AmazonMws\AmazonReportRequest('store1');
//        $amz1->setReportType('_GET_MERCHANT_LISTINGS_ALL_DATA_');
//        //$amz1->setReportType('_GET_SELLER_FEEDBACK_DATA_');
//        $amz1->requestReport();
        //dd($amz1->getReportRequestId());

        $amz = new \Sonnenglas\AmazonMws\AmazonReportRequestList('store1');
        $amz->setMaxCount(1);
        $amz->fetchRequestList();
//        dd($amz->getList());

        //Listening For The Report Processing Status & GeneratedReportId to be set
        while($amz->getList()[0]['GeneratedReportId'] === ""){
            echo $amz->getList()[0]['GeneratedReportId'];
        }

        $amzz = new \Sonnenglas\AmazonMws\AmazonReport('store1');
        $amzz->setReportId($amz->getList()[0]['GeneratedReportId']);
        echo $amzz->fetchReport();
        //echo file_get_contents('/home/rkayx/Documents/test');

        return view('admin.feedback.amazon');
    }
}
