<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AmazonController extends Controller
{
    public function index(){

        $amz1 = new \Sonnenglas\AmazonMws\AmazonReportRequest('store1');
        $amz1->setReportType('_GET_MERCHANT_LISTINGS_ALL_DATA_');
        $amz1->requestReport();

        $amz = new \Sonnenglas\AmazonMws\AmazonReportRequestList('store1');
        $amz->setMaxCount(2);
        $amz->fetchRequestList();
        sleep(3);

        //Listening For The Report Processing Status & GeneratedReportId to be set
        while($amz->getList()[1]['ReportProcessingStatus'] === "_SUBMITTED_"){
            $amz->fetchRequestList();
            echo $amz->getList()[1]['GeneratedReportId'];
        }

        $amzz = new \Sonnenglas\AmazonMws\AmazonReport('store1');
        $amzz->setReportId($amz->getList()[1]['GeneratedReportId']);
        $amzz->fetchReport();
        dd($amzz->getRawResponses()[0]['body']);
        //echo file_get_contents('/home/rkayx/Documents/test');

        return view('admin.feedback.amazon');
    }
}
