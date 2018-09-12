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
use test\Mockery\ArgumentObjectTypeHint;
use mysqli;


class WPCartController extends Controller
{
    public function index()
    {

        /**
         * Create the service object.
         */

        $mysqli = new mysqli(env('WP_DB_HOST',''), env('WP_DB_USERNAME',''), env('WP_DB_PASSWORD',''),env( 'WP_DB_DATABASE',''));

//        $request3 = new Types\GetMemberMessagesRequestType();

        /*
         * This is the "official" OO way to do it,
         * BUT $connect_error was broken until PHP 5.2.9 and 5.3.0.
         */
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') '
                . $mysqli->connect_error);
        }

        /*
         * Use this instead of $connect_error if you need to ensure
         * compatibility with PHP versions prior to 5.2.9 and 5.3.0.
         */
        if (mysqli_connect_error()) {
            die('Connect Error (' . mysqli_connect_errno() . ') '
                . mysqli_connect_error());
        }




        $sql = "SELECT P1.`ID`, P1.`post_title`, P2.`guid` FROM `wp_posts` P1, `wp_posts` P2 WHERE P2.`ID` in (SELECT `meta_value` FROM `wp_postmeta` WHERE `post_id` in (SELECT `ID`  FROM `wp_posts` WHERE `post_type` LIKE \"product\")\n"

            . " AND `meta_key` = \"_thumbnail_id\") AND P1.`post_type` LIKE \"product\" AND P1.`ID` = P2.`post_parent`";

        $result = mysqli_query($mysqli, $sql);
        $rows[] = array();

        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {
                array_push($rows,$row);

            }
        } else {
            echo "0 results";
        }

        return view('admin.products.cart')->with([
            'response' => $rows
        ]);
    }


}
