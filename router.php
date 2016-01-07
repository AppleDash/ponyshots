<?php
$page = $_GET["page"];
$page = !is_array($page) ? trim($page) : trim($page[0]);
$allowed = array("index", "upload");
$default = "index";

if ((!in_array($page, $allowed)) || $page == "") {
    $page = $default;
}

if (!file_exists("pages/${page}.php")) {
    die("Error: Failed to include the page!");
}

require("pages/${page}.php");
?>