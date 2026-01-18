<?php
// Telegram Bot Token
$botToken = "7672249611:AAHOPbCKIOxsUW0G1HmRpMtOq_cS_AVmvP0";
$apiURL = "https://api.telegram.org/bot$botToken/"; # setWebhook?url=https://app.vpsm.co.in/tele/";

// Get incoming update
$update = json_decode(file_get_contents("php://input"), true);

if (isset($update["message"])) {

    $chat_id = $update["message"]["chat"]["id"];
    $text = strtolower(trim($update["message"]["text"]));

    // Set India Timezone
    date_default_timezone_set("Asia/Kolkata");
    $hour = date("H");

    // Greeting based on IST
    if ($hour >= 5 && $hour < 12) {
        $greeting = "Good Morning";
    } elseif ($hour >= 12 && $hour < 17) {
        $greeting = "Good Afternoon";
    } elseif ($hour >= 17 && $hour < 21) {
        $greeting = "Good Evening";
    } else {
        $greeting = "Good Night";
    }

    // Check user message
    if ($text == "hi") {
        $reply = "$greeting 🌸\nWelcome to My Dream ✨";

        file_get_contents($apiURL . "sendMessage?chat_id=$chat_id&text=" . urlencode($reply));
    }
}
?>
