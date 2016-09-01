<?php

namespace Markoftw\AccountChecker\Webrequests;

class Webrequests
{

    protected static function request($service, $url, $use_proxy = false, $proxy = array(), $use_post = false, $post_fields = array())
    {
        $cookies = 'cookies_'.$service.'.txt';

        if (!file_exists($cookies) || !is_writable($cookies))
        {
            echo '<br/>' . $cookies . ' missing or not writable.<br/>';
            exit;
        }

        $user_agent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36';
        $proxy_ip = $proxy[0];

        $proc = curl_init();  // Initialising cURL session

        $header = array();
        $header[] = "Accept:application/json, text/plain, */*";
        $header[] = "Accept-Encoding:gzip, deflate";
        $header[] = "Accept-Language:sl-SI,sl;q=0.8,en-GB;q=0.6,en;q=0.4";
        $header[] = "Connection: keep-alive";
        $header[] = "Content-Type: application/x-www-form-urlencoded";

        if ($use_proxy) { // Checking if proxy is set
            curl_setopt($proc, CURLOPT_PROXY, trim($proxy_ip));
            if ($proxy[1] == "SOCKS5") {
                curl_setopt($proc, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
            } elseif ($proxy[1] == "SOCKS4"){

            } elseif ($proxy[1] == "HTTP"){

            }
        }

        curl_setopt($proc, CURLOPT_URL, $url);
        curl_setopt($proc, CURLOPT_SSL_VERIFYPEER, FALSE); // Prevent cURL from verifying SSL certificate
        curl_setopt($proc, CURLOPT_USERAGENT, $user_agent); // Setting useragent
        curl_setopt($proc, CURLOPT_TIMEOUT, 15);
        curl_setopt($proc, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($proc, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($proc, CURLOPT_FOLLOWLOCATION, TRUE); //Follow Location: headers
        curl_setopt($proc, CURLOPT_RETURNTRANSFER, TRUE); // Returning transfer as a string
        curl_setopt($proc, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($proc, CURLOPT_COOKIEJAR, $cookies); // Setting cookiejar
        curl_setopt($proc, CURLOPT_COOKIESESSION, TRUE);  // Use cookies
        curl_setopt($proc, CURLOPT_REFERER, $url);
        curl_setopt($proc, CURLOPT_COOKIEFILE, $cookies); // Setting cookiefile
        curl_setopt($proc, CURLOPT_VERBOSE, TRUE);
        curl_setopt($proc, CURLOPT_NOBODY, FALSE);

        if ($use_post) {
            //curl_setopt($proc, CURLOPT_HEADER, $header);
            curl_setopt($proc, CURLOPT_HEADER, TRUE);
            curl_setopt($proc, CURLOPT_POST, TRUE); // Setting URL to POST
            curl_setopt($proc, CURLOPT_POSTFIELDS, $post_fields); // Setting POST fields as array
        } else {
            curl_setopt($proc, CURLOPT_HEADER, TRUE);
        }

        $body = curl_exec($proc);
        //$info = curl_getinfo($proc);
        //echo <pre>
        //print_r($info);
        //echo "</pre>";
        if ($body === false) {
            echo '<br/>Curl error: ' . curl_error($body);
        }

        curl_close($proc);

        return $body;
    }

}
