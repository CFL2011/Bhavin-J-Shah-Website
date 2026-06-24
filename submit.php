<?php
/* =====================================================================
   Enquiry receiver — saves form submissions and forwards them to any
   platform (Google Sheet / Notion / Zapier / Make) via a webhook URL,
   plus optional email notification. No database needed.
   ===================================================================== */

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "error" => "method"]);
    exit;
}

$DATA_DIR  = __DIR__ . "/data";
$STORE     = $DATA_DIR . "/enquiries.json";
$SETTINGS  = $DATA_DIR . "/settings.json";

/* Honeypot: bots fill the hidden "website" field -> silently drop */
if (!empty($_POST["website"])) {
    echo json_encode(["ok" => true]);
    exit;
}

/* Collect submitted fields (skip internal/honeypot keys) */
$skip = ["website"];
$fields = [];
foreach ($_POST as $k => $v) {
    if (in_array($k, $skip, true)) continue;
    $fields[$k] = is_array($v) ? implode(", ", $v) : trim($v);
}

$form_type = $fields["form_type"] ?? "Enquiry";
unset($fields["form_type"], $fields["page_url"]);

/* Basic required check */
$hasData = false;
foreach ($fields as $v) { if ($v !== "") { $hasData = true; break; } }
if (!$hasData) {
    http_response_code(422);
    echo json_encode(["ok" => false, "error" => "empty"]);
    exit;
}

$record = [
    "id"        => uniqid("enq_"),
    "date"      => date("Y-m-d H:i:s"),
    "form_type" => $form_type,
    "fields"    => $fields,
    "page_url"  => $_POST["page_url"] ?? "",
    "ip"        => $_SERVER["REMOTE_ADDR"] ?? "",
];

/* ---- 1. Save to local JSON store (with file lock) ---- */
if (!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0775, true);
$all = [];
if (file_exists($STORE)) {
    $all = json_decode(file_get_contents($STORE), true);
    if (!is_array($all)) $all = [];
}
$all[] = $record;
file_put_contents($STORE, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);

/* ---- 2. Load integration settings ---- */
$settings = [];
if (file_exists($SETTINGS)) {
    $settings = json_decode(file_get_contents($SETTINGS), true);
    if (!is_array($settings)) $settings = [];
}

/* ---- 3. Forward to webhook(s) — Google Sheet / Notion / Zapier / Make ---- */
$webhooks = [];
if (!empty($settings["webhook_url"]))  $webhooks[] = trim($settings["webhook_url"]);
if (!empty($settings["webhook_url2"])) $webhooks[] = trim($settings["webhook_url2"]);

$payload = array_merge(
    ["form_type" => $form_type, "date" => $record["date"]],
    $fields,
    ["page_url" => $record["page_url"]]
);

foreach ($webhooks as $hook) {
    if (filter_var($hook, FILTER_VALIDATE_URL)) {
        send_webhook($hook, $payload);
    }
}

/* ---- 4. Optional email notification ---- */
if (!empty($settings["notify_email"]) && filter_var($settings["notify_email"], FILTER_VALIDATE_EMAIL)) {
    $body = "New website enquiry: $form_type\nDate: {$record['date']}\n\n";
    foreach ($fields as $k => $v) $body .= "$k: $v\n";
    @mail($settings["notify_email"], "New Enquiry: $form_type", $body, "From: no-reply@" . ($_SERVER["HTTP_HOST"] ?? "website"));
}

echo json_encode(["ok" => true]);

/* Helper: POST JSON to a webhook (best-effort, short timeout) */
function send_webhook($url, $data) {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (function_exists("curl_init")) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(["http" => [
            "method"  => "POST",
            "header"  => "Content-Type: application/json\r\n",
            "content" => $json,
            "timeout" => 8,
        ]]);
        @file_get_contents($url, false, $ctx);
    }
}
