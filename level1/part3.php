<?php

//case 1
//processHttpRequest("GET", "/sum?nums=1,2,3", " ", " ");
//res: 200

//case 2
//processHttpRequest("GET", "/um?nums=1,2,3", " ", " ");
//res: 404

//case 3
//processHttpRequest("POST", "/sum?nums=1,2,3", " ", " ");
//res: 400

//case 3
//processHttpRequest("GET", "/sum=1,2,3", " ", " ");
// res: 400

// php tester.php 3 part3.php
function readHttpLikeInput()
{
    $f = fopen('php://stdin', 'r');
    $store = "";
    $toread = 0;
    while($line = fgets($f)) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/', $line, $m)) {
            $toread=$m[1]*1;
        }
        if ($line == "\r\n") {
            break;
        }
    }
    if ($toread > 0) {
        $store .= fread($f, $toread);
    }
    return $store;
}

$contents = readHttpLikeInput();

function outputHttpResponse($statuscode, $statusmessage, $headers, $body)
{
    $result = "HTTP/1.1 $statuscode $statusmessage".PHP_EOL
            //   . "Date:" . date("D, d M Y H:i:s T") . "\r\n"
              . "Server: Apache/2.2.14 (Win32)" . PHP_EOL
              . "Connection: Closed" . PHP_EOL
              . "Content-Type: text/html; charset=utf-8" . PHP_EOL
              . "Content-Length: " . strlen($body) . PHP_EOL
              . $body;
    echo($result);
}

//тут весь процес
//зробила по тесту, в прикладі інакше
function processHttpRequest($method, $uri, $headers, $body)
{
    if (strpos($uri, "/sum?nums=") !== false) {
        //1-st case
        if ($method === "GET" && strlen($uri) > 10 && is_numeric($uri[10])) {
            return countSumAndOutputResponse($uri, $headers);
        } elseif ($method !== "GET") {
            return  outputHttpResponse(400, "Bad Request", $headers, "bad request");
        }
    } elseif (strpos($uri, "/sum") !== 0) {
        return  outputHttpResponse(404, "Not Found", $headers, "not found");
    } else {
        return  outputHttpResponse(400, "Bad Request", $headers, "bad request");
    }
}

function countSumAndOutputResponse($uri, $headers)
{
    //substring after "="
    $numsString = substr($uri, 10);

    //get elements
    $numsArray = explode(',', $numsString);

    //count the sum
    $sum = 0;
    for ($i=0; $i < count($numsArray); $i++) {
        $sum += intval($numsArray[$i]);
    }
    outputHttpResponse(200, "OK", $headers, $sum);
}

function parseTcpStringAsHttpRequest($string)
{
    $substrings = explode("\n", $string);
    $firstString = explode(" ", $substrings[0]);
    $method = $firstString[0];
    $uri = $firstString[1];

    $headers = array();

    for ($i = 1; $i < count($substrings) - 1; $i++) {
        $header = explode(": ", $substrings[$i]);
        $headers[$i] = array($header[0], $header[1]);
    }

    $body = $substrings[count($substrings) - 1];
    return array(
        "method" => $method,
        "uri" => $uri,
        "headers" => $headers,
        "body" => $body
    );
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);
