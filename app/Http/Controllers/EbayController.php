<?php

namespace App\Http\Controllers;

use DTS\eBaySDK\Account\Enums\CurrencyCodeEnum;
use GuzzleHttp\Psr7\UriNormalizer;
use Hkonnet\LaravelEbay\Ebay;
use Hkonnet\LaravelEbay\EbayServices;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\Messages\MailMessage;

class EbayController extends Controller
{
    public function index()
    {

        /**
         * Create the service object.
         */
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();

        $request1 = new Types\GetAccountRequestType();
        $request2 = new Types\GetMyeBaySellingRequestType();
        $request3 = new Types\GetMemberMessagesRequestType();

        /**
         * A user token is required when using the Trading service.
         */
        $ebay = new Ebay();
        $authToken = $ebay->getAuthToken();
        $request1->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request1->RequesterCredentials->eBayAuthToken = $authToken;
        $request2->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request2->RequesterCredentials->eBayAuthToken = $authToken;
        $request3->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request3->RequesterCredentials->eBayAuthToken = $authToken;

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
        $response3 = $service->GetMemberMessages($request3);

        return view('admin.feedback.ebay')->with([
            'response1' => $response1,
            'response2' => $response2
        ]);
    }

    public function product($id)
    {
        /**
         * Create the service object.
         */
        $ebay_service = new EbayServices();
        $service = $ebay_service->createTrading();

        $request = new Types\GetMemberMessagesRequestType();

        /**
         * A user token is required when using the Trading service.
         */
        $ebay = new Ebay();
        $authToken = $ebay->getAuthToken();
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $authToken;

        /*
         * Filling the request fields.
         */
        $request->ItemID = $id;
        $request->MailMessageType = "All";

        $response = $service->GetMemberMessages($request);

        return view('admin.feedback.ebay-product')->with([
            'id' => $id,
            'response' => $response
        ]);
    }

    public function answer(Request $request)
    {
        if ($request->ajax()) {
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
            $request1->MemberMessage->Body = $request->body;
            $request1->MemberMessage->ParentMessageID = $request->msgId;
            $request1->MemberMessage->RecipientID = $request->recId;

            $response = $service->AddMemberMessageRTQ($request1);
            return "I am in.";
        }
        return "I did nothin.";
    }
}
