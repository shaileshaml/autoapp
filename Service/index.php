<?php

$botToken = "7672249611:AAHOPbCKIOxsUWOG1HmRpMtOq_cS_AVmPO";
$chatId = "1635904266";
$message = "Hello from PHP via cURL!";

$url = "https://api.telegram.org/bot$botToken/sendMessage";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'chat_id' => $chatId,
    'text' => $message
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
