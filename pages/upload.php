<?php
include_once("inc/db.php");
header("Content-Type: application/json");

function makeerr($msg) {
    return json_encode(array("error" => true, "message" => $msg));
}

function dieerr($m) {
    die(makeerr($m));
}

function generateRandomString($length=10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

$host = strtolower($_SERVER["HTTP_HOST"]);

if (empty($host)) {
    dieerr("No Host header sent");
}

$res = db_query("SELECT * FROM hosts WHERE host=?", [$host]);

if ($res->rowCount() == 0) {
    dieerr("Host not authorized");
}

$host_id = $res->fetch()["id"];

if (!isset($_POST["username"]) || !isset($_POST["apikey"])) {
    dieerr("Username and API key are needed!");
}

$apikey = $_POST["apikey"];

$res = db_query("SELECT * FROM users WHERE username=?", [$_POST["username"]]);

if ($res->rowCount() === 0) {
    dieerr("There is no user by that name!");
}

$arr = $res->fetch();

if ($arr["apikey"] !== $apikey) {
    dieerr("API key provided is invalid for user!");
}

$user_id = $arr["id"];

if (!isset($_FILES["image"])) {
    dieerr("No image file given!");
}

$filename = $_FILES["image"]["name"];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$hash = sha1_file($_FILES["image"]["tmp_name"]);
$slug = null;
$good = false;

while (!$good) {
    $good = true;
    $slug = generateRandomString(7);
    $sres = db_query("SELECT * FROM images WHERE slug=?", [$slug]);
    if ($res->rowCount() > 0) {
        $good = false;
    }
}

if (!in_array($ext, array("gif", "png", "jpg", "jpeg"))) {
    die("Invalid file type uploaded!");
}

if (!file_exists("images/")) {
    mkdir("images/");
}

if (!file_exists("images/${host}")) {
    mkdir("images/${host}");
}

move_uploaded_file($_FILES["image"]["tmp_name"], "images/${host}/${slug}.${ext}");

db_query("INSERT INTO images (user_id, host_id, original_name, hash, slug) VALUES (?, ?, ?, ?, ?)", [$user_id, $host_id, $filename, $hash, $slug]);

echo json_encode(array("error" => false, "hash" => $hash, "slug" => $slug, "extension" => $ext));

?>
