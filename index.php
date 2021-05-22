<?php

header('content-type: application/json');
include 'lib/cs_router.php';

function http_response($bool, $message) {
    $status = 'success';
    if (!$bool) { // failed response
        header('X-API-Response: 406', true, 406);
        $status = 'failed';
    }
    return json_encode(
            array(
                'status' => $status,
                'message' => $message,));
}

$data = false;
$body = file_get_contents('php://input');
if ($body) {
    $data = json_decode($body);
}
$cs = new CS_Router($data);
$cs->route();
echo $cs->http_response();
