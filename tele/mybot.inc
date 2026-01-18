<?php
// Telegram Bot Token
$botToken = "7672249611:AAHOPbCKIOxsUW0G1HmRpMtOq_cS_AVmvP0";
$apiURL = "https://api.telegram.org/bot$botToken/"; # setWebhook?url=https://app.vpsm.co.in/tele/";

date_default_timezone_set("Asia/Kolkata");

// Get Update
$update = json_decode(file_get_contents("php://input"), true);

// Session storage (per user)
function getSession($userId) {
    $file = "sessions/$userId.json";
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

function saveSession($userId, $data) {
    if (!is_dir("sessions")) mkdir("sessions");
    file_put_contents("sessions/$userId.json", json_encode($data));
}

function clearSession($userId) {
    @unlink("sessions/$userId.json");
}

function sendMessage($chat_id, $text, $keyboard = null) {
    global $apiURL;
    $data = [
        "chat_id" => $chat_id,
        "text" => $text,
        "parse_mode" => "HTML"
    ];
    if ($keyboard) {
        $data["reply_markup"] = json_encode($keyboard);
    }
    file_get_contents($apiURL . "sendMessage?" . http_build_query($data));
}

/* ================= HANDLE MESSAGE ================= */

if (isset($update["message"])) {

    $chat_id = $update["message"]["chat"]["id"];
    $userId  = $update["message"]["from"]["id"];
    $text    = trim($update["message"]["text"]);
    $session = getSession($userId);

    // Greeting
    if (strtolower($text) == "hi") {
        $hour = date("H");
        $greet = ($hour < 12) ? "Good Morning" : (($hour < 17) ? "Good Afternoon" : "Good Evening");

        sendMessage($chat_id,
            "$greet 🌸\nWelcome to <b>My Dream</b> ✨",
            [
                "inline_keyboard" => [
                    [["text" => "➕ Add Notes", "callback_data" => "add_notes"]]
                ]
            ]
        );
        clearSession($userId);
        exit;
    }

    // Step handling
    if (($session["step"] ?? "") == "mob") {
        $session["mob"] = $text;
        $session["step"] = "date";
        saveSession($userId, $session);
        sendMessage($chat_id, "📅 Enter Date (dd-mm-yyyy):");
    }

    elseif (($session["step"] ?? "") == "date") {
        $session["date"] = $text;
        $session["step"] = "note";
        saveSession($userId, $session);
        sendMessage($chat_id, "📝 Enter your note:");
    }

    elseif (($session["step"] ?? "") == "note") {
        $session["note"] = $text;
        $session["step"] = "confirm";
        saveSession($userId, $session);

        $msg = "<b>Please confirm your entry</b>\n\n";
        $msg .= "📱 Mobile: {$session['mob']}\n";
        $msg .= "📅 Date: {$session['date']}\n";
        $msg .= "📝 Note:\n{$session['note']}";

        sendMessage($chat_id, $msg, [
            "inline_keyboard" => [
                [
                    ["text" => "✅ Yes", "callback_data" => "save_note"],
                    ["text" => "❌ No", "callback_data" => "cancel_note"]
                ]
            ]
        ]);
    }
}

/* ================= HANDLE BUTTONS ================= */

if (isset($update["callback_query"])) {

    $chat_id = $update["callback_query"]["message"]["chat"]["id"];
    $userId  = $update["callback_query"]["from"]["id"];
    $data    = $update["callback_query"]["data"];
    $session = getSession($userId);

    if ($data == "add_notes") {
        saveSession($userId, ["step" => "mob"]);
        sendMessage($chat_id, "📱 Enter Mobile Number:");
    }

    elseif ($data == "cancel_note") {
        clearSession($userId);
        sendMessage($chat_id, "❌ Entry cancelled. Data cleared.");
    }

    elseif ($data == "save_note") {

        /* ===== DATABASE SAVE (SQL SERVER) ===== */

        $conn = new PDO(
            "sqlsrv:Server=YOUR_SERVER;Database=YOUR_DB",
            "DB_USER",
            "DB_PASS"
        );

        $sql = "INSERT INTO tbl_Notes
                (NoteForId, userid, NoteDetail, Notes_date, notes_mob)
                VALUES (:ForId, :UserId, :Detail, :dt, :mob)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ":ForId"  => 1,
            ":UserId" => $userId,
            ":Detail" => $session["note"],
            ":dt"     => date("Y-m-d", strtotime($session["date"])),
            ":mob"    => $session["mob"]
        ]);

        clearSession($userId);
        sendMessage($chat_id, "✅ Note saved successfully!");
    }
}
?>
