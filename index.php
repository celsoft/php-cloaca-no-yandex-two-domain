<?php

require 'vendor/autoload.php';
require 'Mobile_Detect.php';

$maxmindReader = new \MaxMind\Db\Reader('GeoLite2-ASN.mmdb');
$detect = new Mobile_Detect;

$userIp = preg_replace('/[^\da-f.:]/', '', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');
$ASNArray = $maxmindReader->get($userIp);
$serverRequestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$userIpASN = isset($ASNArray['autonomous_system_number'], $ASNArray['autonomous_system_organization']) ? ($ASNArray['autonomous_system_number'] . ' ' . $ASNArray['autonomous_system_organization']) : '';
//$isSearchBot = (bool)((empty($_SERVER['REMOTE_ADDR']) or $_SERVER['REMOTE_ADDR'] === $userIp) and $userIpASN and preg_match('#(google|mail.ru|yahoo|facebook|seznam|twitter|yandex|vkontakte|telegram)#i', $userIpASN)); #|microsoft|apple
$isSearchBot = (bool)((empty($_SERVER['REMOTE_ADDR']) or $_SERVER['REMOTE_ADDR'] === $userIp) and $userIpASN and preg_match('#(mail.ru|yahoo|facebook|seznam|twitter|yandex|vkontakte|telegram)#i', $userIpASN)); #|microsoft|apple
$serverHttpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$user_agent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
$app_engine = ( isset($_SERVER['HTTP_APP_ENGINE']) ? $_SERVER['HTTP_APP_ENGINE'] : false );

if ($isSearchBot and stripos($userIpASN, 'Google Fiber') !== false) {
    $isSearchBot = false;
}

$oldDomain = 'igrovyeavtomatyc.com';

if ( $isSearchBot ) {

    header('HTTP/1.0 404 Not Found', true, 404);
    die('Error 404');

} else {

    echo curlProxy($oldDomain);

}

function curlProxy($mirror)
{
    global $oldDomain, $redirectDomain, $userAgent;
    $url = "https://{$mirror}{$_SERVER['REQUEST_URI']}";
    // create a new cURL resource
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent . ' AppEngine-Google');
    $result = curl_exec($ch);
    $result = str_replace($oldDomain, $redirectDomain, $result);
    $info = curl_getinfo($ch);
    $contentType = $info['content_type'];
    @header("Content-Type: $contentType");
    // close cURL resource, and free up system resources
    curl_close($ch);
    return $result;
}