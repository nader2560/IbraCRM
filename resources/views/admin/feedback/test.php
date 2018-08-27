<?php
//FEEDBACK
/**
 * The namespaces provided by the SDK.
 */
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;
use \Hkonnet\LaravelEbay\EbayServices;
use Hkonnet\LaravelEbay\Ebay;
/**
 * Create the service object.
 */
$ebay_service = new EbayServices();
$service = $ebay_service->createTrading();

/**
 * Create the request object.
 */
$request = new Types\GetFeedbackRequestType();
/**
 * An user token is required when using the Trading service.
 *
 * NOTE: eBay will use the token to determine which store to return.
 */
$request->RequesterCredentials = new Types\CustomSecurityHeaderType();
$ebay = new Ebay();
$authToken = $ebay->getAuthToken();
//echo $authToken;
$request->RequesterCredentials->eBayAuthToken = $authToken;
/**
 * By specifying 'Positive' we are telling the API return only positive reviews.
 */
//$request->CommentType = ['Positive'];
/**
 * By specifying 'ReturnAll' we are telling the API return the full reviews.
 */
$request->DetailLevel = ['ReturnAll'];
/**
 * Send the request.
 */
$response = $service->getFeedback($request);
/**
 * Output the result of calling the service operation.
 */
if (isset($response->Errors)) {
    foreach ($response->Errors as $error) {
        printf(
            "%s: %s\n%s\n\n",
            $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
            $error->ShortMessage,
            $error->LongMessage
        );
    }
}
//echo($response);
if ($response->Ack !== 'Failure' && $response->PaginationResult->TotalNumberOfEntries != 0) {
    foreach ($response->FeedbackDetailArray->FeedbackDetail as $feedback) {
        printf(
//            "User %s bought %s on %s. Comment: %s<br/>",
//            $feedback->CommentingUser,
//            $feedback->ItemTitle,
//            $feedback->CommentTime->format('d-m-Y H:i'),
//            $feedback->CommentText
        "sewev: %s<br>",
        $feedback->FeedbackResponse
        );
    }
}