<?php
// ================= CONFIG =================
$BOT_TOKEN = "7933371142:AAEBALw_fUjUZ6HldObvJfkgjE_DSOfnkpI";
$API_BASE  = "https://mrnoface.top/magi/main.php?msisdn=";

// ================= BROWSER TEST =================
if (isset($_GET['test'])) {
    $num = $_GET['test'];
    $res = file_get_contents($API_BASE.urlencode($num));
    header("Content-Type: application/json");
    echo $res ?: json_encode(["error"=>"API not responding"]);
    exit;
}

// ================= READ TELEGRAM UPDATE =================
$update = json_decode(file_get_contents("php://input"), true);
if (!$update || !isset($update["message"])) exit;

$chat_id = $update["message"]["chat"]["id"];
$text    = trim($update["message"]["text"] ?? "");

// ================= /start =================
if ($text === "/start") {
    sendMessage($chat_id,
        "🕶️ *NoS Location Bot*\n\n".
        "📱 নাম্বার পাঠাও (019xxxxxxxx)\n".
        "⚡ সাথে সাথে লোকেশন ডাটা",
        true
    );
    exit;
}

// ================= VALIDATE NUMBER =================
if (!preg_match('/^[0-9]{3,15}$/', $text)) {
    sendMessage($chat_id, "❌ সঠিক নাম্বার পাঠাও");
    exit;
}

// ================= CALL API =================
$ch = curl_init($API_BASE.urlencode($text));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 40,
    CURLOPT_USERAGENT => "Mozilla/5.0"
]);
$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    sendMessage($chat_id, "❌ API থেকে কোনো ডাটা আসেনি");
    exit;
}

$data = json_decode($response, true);
if (!is_array($data)) {
    sendMessage($chat_id, "❌ Invalid API response");
    exit;
}

$info = $data["data"] ?? $data;
if (!$info) {
    sendMessage($chat_id, "❌ এই নাম্বারের জন্য ডাটা নেই");
    exit;
}

// ================= FORMAT RESULT =================
$msg = "🕶️ *NoS Result*\n";
foreach ($info as $k => $v) {
    if (is_array($v)) continue;
    $msg .= "• *{$k}*: `{$v}`\n";
}

// ================= SEND =================
sendMessage($chat_id, $msg, true);

// ================= FUNCTION =================
function sendMessage($chat_id, $text, $markdown=false){
    global $BOT_TOKEN;
    $params = [
        "chat_id" => $chat_id,
        "text"    => $text
    ];
    if ($markdown) $params["parse_mode"] = "Markdown";

    file_get_contents(
        "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage?".
        http_build_query($params)
    );
}