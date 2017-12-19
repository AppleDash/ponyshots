<?php
$page = $_GET["page"];
$page = is_array($page) ? trim($page[0]) : trim($page);
$allowed = array("index", "upload");
$default = "index";

if (!in_array($page, $allowed)) {
    $page = $default;
}

if (!file_exists("pages/${page}.php")) {
    die("Error: Requested page does not exist.");
}

require("pages/${page}.php");
?>
