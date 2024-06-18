<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use DOMDocument;
use DOMXPath;

class PaymentMethodController extends Controller
{
    public function paymentApi($payload){
        $parameters = [
            'merchantid' => env('MERCHANT_ID'),
            'txnid' => $payload['transaction_num'],
            'amount' => $payload['amount'],
            'ccy' => 'PHP',
            'description' => 'My purchased',
            'email' => $payload['email'],
            'key' => env('MERCHANT_PASSWORD'),
        ];
        $digest_string = implode(':', $parameters);
        unset($parameters['key']);
        $parameters['digest'] = sha1($digest_string);
        $parameters['procId'] = $payload['proc_id'];
        $parameters['param2'] = env('PARAM2');
        $parameters['param1'] = $payload['type'];
        
        $url = 'https://'.env('TYPE').'.dragonpay.ph/Pay.aspx?';
        $url .= http_build_query($parameters, '', '&');
        return $url;
    }
    public function getRefnoNumer(Request $request)
    {
        $validate = $request->validate([
            'url' => 'required|url'
        ]);
        $url_query_parse = parse_url($validate['url'], PHP_URL_QUERY);
        parse_str($url_query_parse, $url_query_parse_final);

        $client = new Client();
        $url = "https://".env('TYPE').".dragonpay.ph/Pay.aspx";
        $params = $url_query_parse_final;

        $response = $client->request('GET', $url, ['query' => $params]);
        $htmlContent = $response->getBody()->__toString();
        $dom = new DOMDocument;
        @$dom->loadHTML($htmlContent);
        $xpath = new DOMXPath($dom);

        $formElement = $xpath->query('//form')->item(0);

        if ($formElement) {
            $formAction = $formElement->getAttribute('action');
            $refnoValue = '';
            parse_str(parse_url($formAction, PHP_URL_QUERY), $query);
            if (isset($query['refno'])) {
                $refnoValue = $query['refno'];
            }
            return response(['message' => 'Found', 'data' => $refnoValue], 200);
        } else {
            return response(['message' => 'No found data'], 400);
        }
    }
    public function payment($request)
    {
        $payload = [
            'trxnamt' => $request['amount'],
            'merchantcode' => '1621',
            'bankcode' => 'B000',
            'trxndetails' => $request['type'],
            'trandetail1' => $request['transaction_number'],
            'trandetail2' => $request['name'],
            'trandetail3' => $request['email'],
            'trandetail4' => '',
            'trandetail5' => '',
            'trandetail6' => '',
            'trandetail7' => '',
            'trandetail8' => '',
            'trandetail9' => '',
            'trandetail10' => '',
            'trandetail11' => '0',
            'trandetail12' => '0',
            'trandetail13' => '0',
            'trandetail14' => '0',
            'trandetail15' => '0',
            'trandetail16' => '0',
            'trandetail17' => '0',
            'trandetail18' => '0',
            'trandetail19' => '0',
            'trandetail20' => '',
            'callbackurl' => 'https://pos.poolreno.com/',
            'checksum' => hash('sha256',$request['amount'].'1621'.$request['type'].$request['transaction_number'].$request['name'].$request['email'].'000000000'.env('LBP_USERNAME').env('LBP_PASSWORD').env('LBP_CHECKSUM_SECRET_KEY')),
            'username' => env('LBP_USERNAME'),
            'password' => env('LBP_PASSWORD')
        ];
        $queryString = '';
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $queryString .= urlencode($key) . '[]=' . urlencode($subValue) . '&';
                }
            } else {
                $queryString .= urlencode($key) . '=' . str_replace(' ', '%20', urlencode($value)) . '&';
            }
        }
        $baseURL = 'http://222.127.109.129:8080/LBP-LinkBiz-RS/rs/postpayment';
        $requestURL = $baseURL . '?' . rtrim($queryString, '&');
        $client = new Client();
        $response = $client->request('POST', $requestURL);
        $content = $response->getBody()->getContents();

        $data = explode('|', $content);

        return $data[1];
    }
}
