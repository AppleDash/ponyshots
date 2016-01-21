<?php
$db_host = "host.int";
$db_user = "ponyshots";
$db_pass = "p@zzw0rd";
$db_base = "ponyshots";

$db_handle = new PDO("mysql:host=${db_host};dbname=${db_base}", $db_user, $db_pass);
$db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function db_query($query, $params=null) {
    global $db_handle;

    $stmt = $db_handle->prepare($query);
    $stmt->execute($params);

    return $stmt;
}

?>
