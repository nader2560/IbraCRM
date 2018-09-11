<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GumtreeController extends Controller
{

    public function getMessages($cookie)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://my.gumtree.com/conversations");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Dnt: 1";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        $headers[] = "X-Hola-Request-Id: 46988";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
        $headers[] = $cookie;
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Hola-Unblocker-Bext: reqid 46988: before request, send headers";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    public function getThread($id)
    {
        $cookiefile = "./cookies-1.txt";
        $string = file_get_contents($cookiefile);
        $json_a = json_decode($string, true);
        $string = "Cookie: ";
        for ($i = 0; $i < count($json_a); $i++) {

            $string .= $json_a[$i]['name'] . '=' . $json_a[$i]['value'] . "; ";
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://my.gumtree.com/conversations/" . $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Dnt: 1";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        $headers[] = "X-Hola-Request-Id: 46988";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
        $headers[] = $string;
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Hola-Unblocker-Bext: reqid 46988: before request, send headers";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $map = json_decode($result);

        //dd($map);

        return view('admin.feedback.gumtree-threadmsg')->with([
            'msgs' => $map->messages,
            'id' => $map->conversationId,
            'prodId' => $map->advert->id
        ]);
    }

    public function getBetween($string, $start = "", $end = "")
    {
        if (strpos($string, $start)) { // required if $start not exist in $string
            $startCharCount = strpos($string, $start) + strlen($start);
            $firstSubStr = substr($string, $startCharCount, strlen($string));
            $endCharCount = strpos($firstSubStr, $end);
            if ($endCharCount == 0) {
                $endCharCount = strlen($firstSubStr);
            }
            return substr($firstSubStr, 0, $endCharCount);
        } else {
            return '';
        }
    }

    public function getstats($cookie)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://my.gumtree.com/manage/ads");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Pragma: no-cache";
        $headers[] = "Dnt: 1";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        $headers[] = "X-Hola-Request-Id: 39374";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
        $headers[] = "Cache-Control: no-cache";
        $headers[] = "Referer: https://my.gumtree.com/manage/messages";
        $headers[] = $cookie;
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Hola-Unblocker-Bext: reqid 39374: before request, send headers";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $ads = new \DOMDocument();

        libxml_use_internal_errors(TRUE);
        $ads->loadHTML($result);
        $xpath = new \DOMXPath($ads);
        $row = $xpath->query('/html/body/script[2]/text()')->item(0);
        $json = $this->getBetween($row->textContent, "madads: [", "]");
        //$json = "myads[".$json."]";
        $json = "[" . $json . "]";
        return $json;
    }

    public function send1Msg(Request $request)
    {
        $cookiefile = "./cookies-1.txt";
        $string = file_get_contents($cookiefile);
        $json_a = json_decode($string, true);
        $string = "Cookie: ";
        for ($i = 0; $i < count($json_a); $i++) {
            $string .= $json_a[$i]['name'] . '=' . $json_a[$i]['value'] . "; ";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://my.gumtree.com/conversations/" . $request->input('id') . "/ message");
        curl_setopt($ch, CURLOPT_TIMEOUT, -1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{ad_id: ' . $request->input('ad_id') . ', message: "' . $request->input('message') . '", sender_email: "i_anwar_22@yahoo.co.uk"}');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_REFERER, 0);
        $headers = array();
        $headers[] = "Origin: https://my.gumtree.com";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7";
        $headers[] = "X-Hola-Request-Id: 77650";
        $headers[] = "X-Requested-With: XMLHttpRequest";
        $headers[] = $string. " eCG_eh=ec=Conversation:ea=MessageSendAttempt:el=null:pt=Conversation:url=https://my.gumtree.com/manage/messages?conversationId=" . $request->input('id') . ":cc=1:lc=10000392:aid=1312162491:";
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Distil-Ajax: fcfxdfwcwavvtvzewaafsewarbtsfcvq";
        $headers[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/68.0.3440.106 Chrome/68.0.3440.106 Safari/537.36";
        $headers[] = "Content-Type: application/json; charset=UTF-8";
        $headers[] = "Accept: */*";
        $headers[] = "Referer: https://my.gumtree.com/manage/messages?conversationId=" . $request->input('id');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $map = json_decode($result);
        return new Response($map, 200);
    }

    public function sendMsg(Request $request)
    {
        $cookiefile = "./cookies-1.txt";
        $string = file_get_contents($cookiefile);
        $json_a = json_decode($string, true);
        $string = "Cookie: ";
        for ($i = 0; $i < count($json_a); $i++) {
            $string .= $json_a[$i]['name'] . '=' . $json_a[$i]['value'] . "; ";
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://my.gumtree.com/conversations/". $request->input('id') . "/message");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{ad_id:  '. $request->input('ad_id') .' , message: "'. $request->input('message') .'" , sender_email: "i_anwar_22@yahoo.co.uk"}');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Origin: https://my.gumtree.com";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: en-US,en;q=0.9";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/68.0.3440.106 Chrome/68.0.3440.106 Safari/537.36";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Accept: */*";
        $headers[] = "Referer: https://my.gumtree.com/manage/messages?conversationId=". $request->input('id');
        $headers[] = $string . " eCG_eh=ec=Conversation:ea=MessageSendAttempt:el=null:pt=Conversation:url=https://my.gumtree.com/manage/messages?conversationId=" . $request->input('id') . ":aid=" . $request->input('ad_id') . ":";
        $headers[] = "Connection: keep-alive";
        $headers[] = "X-Distil-Ajax: fcfxdfwcwavvtvzewaafsewarbtsfcvq";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }

    public function index()
    {

        $cookiefile = "./cookies-1.txt";
        $string = file_get_contents($cookiefile);
        $json_a = json_decode($string, true);
        $string = "Cookie: ";
        for ($i = 0; $i < count($json_a); $i++) {

            $string .= $json_a[$i]['name'] . '=' . $json_a[$i]['value'] . "; ";
        }

        $map = json_decode($this->getstats($string));
        //dd($map);
        $numViews = 0;
        $urdmsgs = array();
        foreach ($map as $ad) {
            $numViews += $ad->listingViews;
            array_push($urdmsgs, $this->countMsgs($ad->adId));
        }

//        foreach(json_decode($this->getstats($string)) as $ad){
//            array_push($map,$ad->adId);
//        };

        return view('admin.feedback.gumtree')->with([
            'ads' => $map,
            'numV' => $numViews,
            'urdmsgs' => $urdmsgs
        ]);
    }

    public function product($id)
    {
        $cookiefile = "./cookies-1.txt";
        $string = file_get_contents($cookiefile);
        $json_a = json_decode($string, true);
        $string = "Cookie: ";
        for ($i = 0; $i < count($json_a); $i++) {

            $string .= $json_a[$i]['name'] . '=' . $json_a[$i]['value'] . "; ";
        }

        $mapAd = json_decode($this->getstats($string));
        //dd(count($map));
        $i = 0;
        while (count($mapAd) > $i)
            if ($mapAd[$i]->adId <> $id)
                $i++;
            else
                break;

        if ($i >= count($mapAd))
            return new Response("Product not found, Ad Id incorrect");
        //dd($i);
        //dd($mapAd[$i]);
        $infos = $mapAd[$i];
        //dd($infos);

        $mapMsgs = json_decode($this->getMessages($string));
        //dd($mapMsgs);
        //dd($infos);
        $prodmsgs = array();
        foreach ($mapMsgs->conversationGroups as $disc) {
            //dd($disc->conversations);
            if ($disc->advert->id == $id && $disc->conversations[0]->userRole === "Seller")
                array_push($prodmsgs, $disc->conversations[0]);
        }

        $sellingBoolean = false;
        if (count($prodmsgs) <> 0)
            $sellingBoolean = true;

        //dd($prodmsgs);

        return view('admin.feedback.gumtree-product')->with([
            'infos' => $infos,
            'msgsAll' => $mapMsgs,
            'msgs' => $prodmsgs,
            'sellingBoolean' => $sellingBoolean
        ]);
    }

    public function countMsgs($id)
    {
        $cookiefile = "./cookies-1.txt";
        $string = file_get_contents($cookiefile);
        $json_a = json_decode($string, true);
        $string = "Cookie: ";
        for ($i = 0; $i < count($json_a); $i++) {

            $string .= $json_a[$i]['name'] . '=' . $json_a[$i]['value'] . "; ";
        }

        $mapMsgs = json_decode($this->getMessages($string));
        //dd($mapMsgs);
        //dd($infos);
        $prodmsgs = array();
        foreach ($mapMsgs->conversationGroups as $disc) {
            //dd($disc->conversations);
            if ($disc->advert->id == $id && $disc->conversations[0]->userRole === "Seller")
                array_push($prodmsgs, $disc->conversations[0]);
        }

        $i = 0;

        if (count($prodmsgs) > 0) {
            foreach ($prodmsgs as $thread) {
                if ($thread->unread == true)
                    $i++;
            }
        }

        return $i;
    }

}
