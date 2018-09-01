<?php

namespace App\Http\Controllers;

use DTS\eBaySDK\Account\Enums\CurrencyCodeEnum;
use DTS\eBaySDK\Types\RepeatableType;
use GuzzleHttp\Psr7\UriNormalizer;
use Hkonnet\LaravelEbay\Ebay;
use Hkonnet\LaravelEbay\EbayServices;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\Messages\MailMessage;
use test\Mockery\ArgumentObjectTypeHint;

class EbayController extends Controller
{
    public function index()
    {

        set_time_limit(600);

        /**
         * Create the service object.
         */
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();

        $request1 = new Types\GetAccountRequestType();
        $request2 = new Types\GetMyeBaySellingRequestType();
//        $request3 = new Types\GetMemberMessagesRequestType();

        /**
         * A user token is required when using the Trading service.
         */
        $ebay = new Ebay();
        $authToken = $ebay->getAuthToken();
        $request1->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request1->RequesterCredentials->eBayAuthToken = $authToken;
        $request2->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request2->RequesterCredentials->eBayAuthToken = $authToken;
//        $request3->RequesterCredentials = new Types\CustomSecurityHeaderType();
//        $request3->RequesterCredentials->eBayAuthToken = $authToken;

        /*
         * Filling the request fields.
         */
        $request1->Currency = CurrencyCodeEnum::C_GBP;
        $request1->ExcludeBalance = false;
        $request2->ActiveList = new Types\ItemListCustomizationType();
        $request2->ActiveList->Include = true;
        $request2->ActiveList->Sort = Enums\ItemSortTypeCodeType::C_TIME_LEFT;

        $response1 = $service->getAccount($request1);
        $response2 = $service->getMyeBaySelling($request2);

//        $idArrays = array();
//        foreach($response2->ActiveList->ItemArray->Item as $item){
//            array_merge($idArrays,array($item->ItemID));
//        }
//
//        $msgsCount = array();
//        for($i=0;$i<count($idArrays);$i++){
//            $request3->ItemID = $idArrays[$i];
//            $request3->MailMessageType = "All";
//            $request3->DisplayToPublic = false;
//            $response3 = $service->GetMemberMessages($request3);
//            $in = 0;
//            if ($response3->PaginationResult->TotalNumberOfEntries <> 0) {
//                foreach ($response3->MemberMessage->MemberMessageExchange as $discussion) {
//                    if ($discussion->MessageStatus <> "Answered") {
//                        $in++;
//                    }
//                }
//            }
//            array_merge($msgsCount,array($in));
//        }
        // To add to return result if ever it works: 'msgCounts' => $msgsCount

        //SecondMethod
        $msgsCount = array();
        foreach ($response2->ActiveList->ItemArray->Item as $item) {
            array_push($msgsCount, $this->countMsgs($item->ItemID));
        }

//        dd($msgsCount);

        return view('admin.feedback.ebay')->with([
            'response1' => $response1,
            'response2' => $response2,
            'msgsCount' => $msgsCount
        ]);
    }

    public function product($id)
    {
        /**
         * Create the service object.
         */
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();
        $service2 = $ebay_service->createShopping();

        $request = new Types\GetMemberMessagesRequestType();
        $request2 = new Types\GetFeedbackRequestType();
        $request3 = new \DTS\eBaySDK\Shopping\Types\GetSingleItemRequestType();
        $request4 = new Types\GetItemTransactionsRequestType();

        /**
         * A user token is required when using the Trading service.
         */
        $ebay = new Ebay();
        $authToken = $ebay->getAuthToken();
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $authToken;
        $request2->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request2->RequesterCredentials->eBayAuthToken = $authToken;
        $request4->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request4->RequesterCredentials->eBayAuthToken = $authToken;

        /*
         * Filling the request fields.
         */
        $request->ItemID = $id;
        $request->MailMessageType = "All";
        $request->DisplayToPublic = false;
        $request2->DetailLevel = ['ReturnAll'];
        $request2->ItemID = $id;
        $request3->ItemID = $id;
        $request4->ItemID = $id;

        $response = $service->GetMemberMessages($request);
        $response2 = $service->GetFeedback($request2);
        $response3 = $service2->GetSingleItem($request3);
        //$response4 = $service->GetItemTransactions($request4);

        //dd($response4);

        //dd(count($response->MemberMessage->MemberMessageExchange));

        $msgs = array();
        for ($i = 0; $i < count($response->MemberMessage->MemberMessageExchange); $i++) {
            $msgs = array_merge(array($response->MemberMessage->MemberMessageExchange[$i]), $msgs);
        }

        //dd($msgs);

        $name = $response3->Item->Title;
        $i = 0;

        if ($response->PaginationResult->TotalNumberOfEntries <> 0) {
            foreach ($response->MemberMessage->MemberMessageExchange as $discussion) {
                if ($discussion->MessageStatus <> "Answered") {
                    $i++;
                }
            }
        }
        return view('admin.feedback.ebay-product')->with([
            'id' => $id,
            'response' => $response,
            'msgs' => $msgs,
            'response2' => $response2,
            'itemName' => $name,
            'msgCount' => $i
        ]);
    }

    public function answer(Request $request)
    {
        /**
         * Create the service object.
         */
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();

        $request1 = new Types\AddMemberMessageRTQRequestType();

        /**
         * A user token is required when using the Trading service.
         */
        $ebay = new Ebay();
        $authToken = $ebay->getAuthToken();
        $request1->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request1->RequesterCredentials->eBayAuthToken = $authToken;

        /*
         * Filling the request fields.
         */
        $request1->MemberMessage = new Types\MemberMessageType();
        $request1->MemberMessage->ParentMessageID = $request->input('msgId');
        $request1->MemberMessage->RecipientID = [$request->input('recId')];
        $request1->MemberMessage->Body = $request->input('body');

        $response = $service->AddMemberMessageRTQ($request1);

        return new Response($response, 200);
    }

    public function answerfb(Request $request)
    {
        /**
         * Create the service object.
         */
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();

        $request1 = new Types\RespondToFeedbackRequestType();

        /**
         * A user token is required when using the Trading service.
         */
        $ebay = new Ebay();
        $authToken = $ebay->getAuthToken();
        $request1->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request1->RequesterCredentials->eBayAuthToken = $authToken;

        /*
         * Filling the request fields.
         */
        $request1->FeedbackID = $request->input('msgId');
        $request1->TargetUserID = $request->input('recId');
        $request1->ResponseType = "Reply";
        $request1->ResponseText = $request->input('body');

        $response = $service->RespondToFeedback($request1);

        return new Response($response, 200);
    }

    public function countMsgs($id)
    {

        /**
         * Create the service object.
         */
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();

        /**
         * A user token is required when using the Trading service.
         */
        $ebay = new Ebay();
        $authToken = $ebay->getAuthToken();
        $request3 = new Types\GetMemberMessagesRequestType();
        $request3->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request3->RequesterCredentials->eBayAuthToken = $authToken;

        $request3->ItemID = $id;
        $request3->MailMessageType = "All";
        $request3->DisplayToPublic = false;
        $response3 = $service->GetMemberMessages($request3);

        $i = 0;
        if ($response3->PaginationResult->TotalNumberOfEntries <> 0) {
            foreach ($response3->MemberMessage->MemberMessageExchange as $discussion) {
                if ($discussion->MessageStatus <> "Answered") {
                    $i++;
                }
            }
        }
        return $i;
    }
}
