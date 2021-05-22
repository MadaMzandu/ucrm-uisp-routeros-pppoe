<?php

header('content-type: application/json');
include 'lib/cs_router.php';

$data = false;
$body = file_get_contents('php://input');
if ($body) {
    $data = json_decode($body);
}
$app = new CS_Router($data);
$app->route();
echo $app->http_response();
