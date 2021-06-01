<?php
/* use to import accounts from previous versions =< 1.14
 * copy your old config.php,gateways.json and ids.json
 * into this folder and run php import.php
*/

include '../lib/routeros_api.class.php';
include '../lib/cs_sqlite.php';
$conf = include 'config.php';
$device_file = 'gateways.json';
$ids_file = 'ids.json';

$routers = json_decode(file_get_contents($device_file));
$ids = json_decode(file_get_contents($ids_file));
$res = [];
$users = [];
$router_names = array_keys((array) $routers);

foreach ($router_names as $name) {
    $api = new RouterosAPI();
    //$api->debug = true;
    $api->connect($routers->{$name}, $conf->api_user, $conf->api_pass);
    $api->write('/ppp/secret/print', false);
    $api->write('?comment');
    $res[$name] = $api->read();
}

foreach ($router_names as $name) {
    foreach ($res[$name] as $item) {
        if (!is_numeric($item['comment'])) {
            continue;
        }
        $users[] = (object) array(
                    'id' => $item['comment'],
                    'address' => $item['remote-address'],
                    'device' => $name,
        );
    }
}
$db = new CS_SQLite('../data/.data.db');
$added=0;
$count = 0;
$valid =0;
$failed = 0;
foreach ($users as $user) {
    $count++;
    if (property_exists($ids,$user->id)) {
        $valid++;
        $var = $db->insert($user, 'services');
        if ($var) {
            $added++;
            continue;
        }
    }
    $failed++;
}
echo $count. " pppoe accounts were found on devices.\n";
echo $valid. " pppoe accounts were recognised by this module.\n";
echo $added."/".$valid . " pppoe accounts were imported.\n";

