<?php
include_once("inc/db.php");
header("Content-Type: application/json");

function makeerr($msg) {
    return json_encode(array("error" => true, "message" => $msg));
}

function dieerr($m) {
    global $db;
    mysqli_close($db);
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

$escaped_host = mysqli_real_escape_string($db, $host);
$res = mysqli_query($db, "SELECT * FROM hosts WHERE host='${escaped_host}'");

if (mysqli_num_rows($res) == 0) {
    dieerr("Host not authorized");
}

$arr = mysqli_fetch_assoc($res);
$host_id = $arr["id"];

if (!isset($_POST["username"]) || !isset($_POST["apikey"])) {
    dieerr("Username and API key are needed!");
}

$username = mysqli_real_escape_string($db, $_POST["username"]);
$apikey = mysqli_real_escape_string($db, $_POST["apikey"]);

$q = "SELECT * FROM users WHERE username='${username}'";
$res = mysqli_query($db, $q);

if (mysqli_num_rows($res) === 0) {
    dieerr("There is no user by that name!");
}

$arr = mysqli_fetch_assoc($res);
if ($arr["apikey"] !== $apikey) {
    dieerr("API key provided is invalid for user!");
}

$user_id = $arr["apikey"];

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
    $sres = mysqli_query($db, "SELECT * FROM images WHERE slug='${slug}'");
    if (mysqli_num_rows($sres) > 0) {
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

$escaped_filename = mysqli_real_escape_string($db, $filename);
$q = "INSERT INTO images (user_id, host_id, original_name, hash, slug) VALUES (${user_id}, ${host_id}, '${escaped_filename}', '${hash}', '${slug}')";
mysqli_query($db, $q);

echo json_encode(array("error" => false, "hash" => $hash, "slug" => $slug, "extension" => $ext));
mysqli_close($db);

?>
