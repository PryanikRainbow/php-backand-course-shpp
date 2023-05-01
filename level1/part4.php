<?php

// bad request
// php level1/tester.php 4 level1/part4.php

//case 1
//in the tester) 200 ОК
// $method = "POST";
// $uri = "/api/checkLoginAndPassword";
// $headers = array("Content-Type" => "application/x-www-form-urlencoded");
// $body = "login=student&password=12345";
// processHttpRequest($method, $uri, $headers, $body);

//case 2 incorrect uri 400 BAD REQUEST
// $method = "POST";
// $uri = "/api/checkLogin";
// $headers = array("Content-Type" => "application/x-www-form-urlencoded");
// $body = "login=test_user&password=12345678";
// processHttpRequest($method, $uri, $headers, $body);


//case 3 incorrect content 400 BAD REQUEST
// $method = "POST";
// $uri = "/api/checkLoginAndPassword";
// $headers = array("Content-Type" => "application/x");
// $body = "login=test_user&password=12345678";
// $response = processHttpRequest($method, $uri, $headers, $body);
// echo $response;

//case 4 not found in file
// 401 Unauthorized
// $method = "POST";
// $uri = "/api/checkLoginAndPassword";
// $headers = array("Content-Type" => "application/x-www-form-urlencoded");
// $body = "login=todo&password=woo";
// processHttpRequest($method, $uri, $headers, $body);


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

//HTML-BODY
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

//тут весь процес в прикладі і тесті по різному
function processHttpRequest($method, $uri, $headers, $body)
{
    $boolUriAndContentType = boolUriAndContentType($headers, $uri);

    //if uri != uri && content-type != content-type
    if (!$boolUriAndContentType) {
        return  outputHttpResponse(400, "Bad Request", $headers, "bad request");
    }

    //if body correct
    $min_body_len = 18;
    if (strlen($body) >= $min_body_len && strpos($body, "login=") === 0 && strpos($body, "&") !== false) {
        $login = substr($body, 6, strpos($body, "&") - 6);
        // echo($body);
        if (strpos($body, "&")+1 === (strpos($body, "password="))) {
            // $password = substr($body, strpos($body, "&") + 1);
            $password = substr($body, strpos($body, "&password=") + 10);

            //if passwords.txt  is missing
            if (!file_exists("passwords.txt")) {
                return outputHttpResponse(
                    500,
                    "Internal Server Error",
                    $headers,
                    "500\nInternal Server Error"
                );
            }

            //data structure - string
            $passwords = file_get_contents("passwords.txt");
            $arrayPasswords = explode("\n", $passwords);

            for ($i=0; $i < count($arrayPasswords); $i++) {
                $log_pass = explode(":", $arrayPasswords[$i]);
                if ($log_pass[0] === $login && $log_pass[1] === $password) {
                    //"<h1 style=\"color:green\">FOUND</h1>";
                    return outputHttpResponse(
                        200,
                        "OK",
                        $headers,
                        "<h1 style=\"$login:$password\">FOUND</h1>"
                    );
                }
            }
            //401
            return outputHttpResponse(401, "Unauthorized", $headers, "401\nUnauthorized");
        }
    }
    return  outputHttpResponse(400, "Bad Request", $headers, "bad request");
}


function boolUriAndContentType($headers, $uri)
{
    return isset($headers["Content-Type"]) && $headers["Content-Type"] === "application/x-www-form-urlencoded" && $uri === "/api/checkLoginAndPassword";
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
        $headers[$header[0]] = $header[1];
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
