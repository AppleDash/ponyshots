<?php
$db_host = "host.int";
$db_user = "ponyshots";
$db_pass = "p@zzw0rd";
$db_base = "ponyshots";

$db_handle = new PDO("pgsql:host=${db_host};dbname=${db_base}", $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);
$db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function db_query($query, $params=null) {
    global $db_handle;

    $stmt = $db_handle->prepare($query);
    $stmt->execute($params);

    return $stmt;
}

?>
