<?php

//use Sonnenglas\AmazonMws\AmazonOrderList;
//
//function getAmazonOrders() {
//    $amz = new AmazonOrderList("store1"); //store name matches the array key in the config file
//    $amz->setLimits('Modified', "- 24 hours");
//    $amz->setFulfillmentChannelFilter("MFN"); //no Amazon-fulfilled orders
//    $amz->setOrderStatusFilter(
//        array("Unshipped", "PartiallyShipped", "Canceled", "Unfulfillable")
//    ); //no shipped or pending
//    $amz->setUseToken(); //Amazon sends orders 100 at a time, but we want them all
//    $amz->fetchOrders();
//    return $amz->getList();
//}
//
//$amz1 = new \Sonnenglas\AmazonMws\AmazonReportRequest('store1');
//$amz1->setReportType('_GET_MERCHANT_LISTINGS_ALL_DATA_');
////$amz1->setReportType('_GET_SELLER_FEEDBACK_DATA_');
//$amz1->requestReport();
//echo($amz1->getReportRequestId());

$amz = new \Sonnenglas\AmazonMws\AmazonReportRequestList('store1');
$amz->setMaxCount(1);
$amz->fetchRequestList();
//dd($amz->getList());

//foreach ($amz->getList() as $rep) {
//    echo $rep['StartDate'].'<br>';
//}

//foreach ($amz->getList() as $rep) {
//    $amzz = new \Sonnenglas\AmazonMws\AmazonReport('store1');
//    $amzz->setReportId($rep['GeneratedReportId']);
//    $amzz->fetchReport();
//    echo count($amzz->getRawResponses());
//}
//
$amzz = new \Sonnenglas\AmazonMws\AmazonReport('store1');
$amzz->setReportId($amz->getList()[0]['GeneratedReportId']);
$amzz->fetchReport();
dd($amzz->saveReport('/home/rkayx/Documents/eDonec/IcingHouseCRM/trials/test'));
//echo file_get_contents('/home/rkayx/Documents/test');
