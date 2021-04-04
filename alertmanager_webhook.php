<?php

$headers = getallheaders();
$received_auth = $headers['Authorization'];

$generate_pass = base64_encode('login:yourpass');
$proper_auth = "Basic {$generate_pass}";
if($received_auth != $proper_auth) return;

function sms_send($params, $token, $backup = false)
{

    static $content;

    if ($backup == true) {
        $url = 'https://api2.smsapi.pl/sms.do';
    } else {
        $url = 'https://api.smsapi.pl/sms.do';
    }

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $params);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer $token"
    ));

    $content = curl_exec($c);
    $http_status = curl_getinfo($c, CURLINFO_HTTP_CODE);

    if ($http_status != 200 && $backup == false) {
        $backup = true;
        sms_send($params, $token, $backup);
    }

    curl_close($c);
    return $content;
}

function vms_send($params, $token)
{

    static $content;

    $url = 'https://api.smsapi.pl/vms.do';

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $params);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer $token"
    ));

    $content = curl_exec($c);

    curl_close($c);
    return $content;
}

$my_json = file_get_contents('php://input');
 
$myArray = json_decode($my_json, true);

$token = "yourtoken";

date_default_timezone_set('Europe/Warsaw');
$currentTime = time();
$currentHour = ((int) date('H', $currentTime));
$currentMinute = ((int) date('i', $currentTime));

if ($currentHour > 8 && $currentHour < 22) {

  $params = array(
      'to' => '48123456789', //numery odbiorców rozdzielone przecinkami
      'from' => 'RODUQ', //pole nadawcy stworzone w https://ssl.smsapi.pl/sms_settings/sendernames
      'message' => $myArray['commonAnnotations']['description'], //treść wiadomości
      'format' => "json"
  );
  echo sms_send($params, $token);

  $params = array(
      'to' => '48123456789', //numery odbiorców rozdzielone przecinkami
      'tts' => 'Uwaga! Zgłoszono alert systemu, pozostałe wytyczne zostały wysłane wiadomością SMS.', //treść wiadomości
      'format' => "json"
  );
  echo vms_send($params, $token);

} else {

  $params = array(
      'to' => '48123456789', //numery odbiorców rozdzielone przecinkami
      'from' => 'RODUQ', //pole nadawcy stworzone w https://ssl.smsapi.pl/sms_settings/sendernames
      'message' => $myArray['commonAnnotations']['description'], //treść wiadomości
      'format' => "json"
  );
  echo sms_send($params, $token);


  $closestTime = new DateTime();
  $closestTime->setTimestamp($currentTime);

  if($currentHour >= 0 && $currentHour < 8){
    $closestTime->setTime(8, 0, 0, 0);
  } else {
    $closestTime->modify('+1 day');
    $closestTime->setTime(8, 0, 0, 0);
  }

  $params = array(
      'to' => '48123456789', //numery odbiorców rozdzielone przecinkami
      'tts' => "Uwaga! O godzinie {$currentHour}:{$currentMinute} zgłoszono alert systemu, pozostałe wytyczne zostały wysłane wiadomością SMS.",
      'date' => $closestTime->getTimestamp(),
      'format' => "json"
  );
  echo vms_send($params, $token);

}

?>