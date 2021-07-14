<?php

use JetBrains\PhpStorm\NoReturn;

require_once("inc/db.php");
require_once("inc/settings.php");
header("Content-Type: application/json");

#[NoReturn]
function die_with_error(string $message) : void {
    die(json_encode([
        'error' => true,
        'message' => $message
    ]));
}

function generateSlug() : string {
    do {
        $encoded_bytes = base64_encode(random_bytes(6));
        $seven_bytes = substr(
            str_replace(['/', '+'], '', $encoded_bytes),
            0, 7
        );
    } while (strlen($seven_bytes) !== 7); /* This can happen if the base64 contained both a slash AND a plus */

    return $seven_bytes;
}


if (empty($_SERVER["HTTP_HOST"])) {
    die_with_error("No Host header sent");
}

if (empty($_POST["username"]) || empty($_POST["api_key"])) {
    die_with_error("Username and API key are needed!");
}

if (!isset($_FILES["image"])) {
    die_with_error("No image file given!");
}

$original_file_name = $_FILES["image"]["name"];
$original_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));

if (!in_array($original_extension, array("gif", "png", "jpg", "jpeg"))) {
    die_with_error("Invalid file type uploaded!");
}

$host = strtolower($_SERVER["HTTP_HOST"]);
$username = $_POST['username'];
$api_key = $_POST['api_key'];

$res = db_query("SELECT id, url_format FROM hosts WHERE host = ?", [$host]);
$host_row = $res->fetch();

if (!$host_row) {
    die_with_error("Host not authorized");
}

$host_id = $host_row['id'];
$url_format = $host_row['url_format'];

$number_of_placeholders = substr_count($url_format, '%s');

if ($number_of_placeholders < 1 || $number_of_placeholders > 2) {
    die_with_error('Internal error (invalid URL format)');
}

if (!file_exists("${settings['upload_path']}/${host}")) {
    mkdir("${settings['upload_path']}/${host}");
}

$res = db_query("SELECT id FROM users WHERE username = ? AND api_key = ?", [$username, $api_key]);
$user_row = $res->fetch();

if (!$user_row) {
    die_with_error("Invalid username or API key.");
}

$user_id = $user_row["id"];
$hash = hash_file('SHA512', $_FILES["image"]["tmp_name"]);
$slug = null;

do {
    $slug = generateSlug();
} while (db_query('SELECT 1 FROM uploads WHERE slug = ?', [$slug])->fetch()); /* While slug exists in DB */


move_uploaded_file($_FILES["image"]["tmp_name"], "${settings['upload_path']}/${host}/${slug}.${original_extension}");

db_query("INSERT INTO uploads (user_id, host_id, original_name, hash, slug) VALUES (?, ?, ?, ?, ?)", [$user_id, $host_id, $filename, $hash, $slug]);

/* placeholder count can only be 1 or 2 as per check above */
$image_url = $number_of_placeholders == 1
                ? sprintf($url_format, $slug)
                : sprintf($url_format, $slug, $original_extension);

echo json_encode([
    'error' => false,
    'url' => $image_url
]);
