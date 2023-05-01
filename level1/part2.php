<?php

// php level1/tester.php 2 level1/part2.php
// не обращайте на эту функцию внимания
// она нужна для того чтобы правильно считать входные данные
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

// 2D array заміість асоціативного?

function parseTcpStringAsHttpRequest($string)
{
    $substrings = explode("\n", $string);
    $firstString = explode(" ", $substrings[0]);
    $method = $firstString[0];
    $uri = $firstString[1];

    $headers = array();

    for ($i = 1; $i < count($substrings) - 1; $i++) {
        $header = explode(": ", $substrings[$i]);
        $headers[] = array($header[0], $header[1]);
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
echo(json_encode($http, JSON_PRETTY_PRINT));
