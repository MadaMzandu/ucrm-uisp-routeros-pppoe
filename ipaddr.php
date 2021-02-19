<?php
$conf = include('config.php');
function ip_release($ip){
  global $conf ;
  $file = file_get_contents($conf->ip_addr_file);
  $ips = json_decode($file,true);
  $check = true ;
  $i = 0 ;
  $issue = '';
  while($check && $i < sizeof($ips)){
    $n = $i++ ;
    $address = $ips[$n]['ip'] ;
    if($ip != $address) continue ;
    $ips[$n]['used'] = false;
    $check = false ;
  }
$json = json_encode($ips);
file_put_contents($conf->ip_addr_file,$json);

}

function ip_issue(){
  global $conf ;
  $file = file_get_contents($conf->ip_addr_file);
  $ips = json_decode($file,true);
  $check = true ;
  $i = 0 ;
  $issue = '';
  while($check && $i < sizeof($ips)){
    $n = $i++ ;
    $is_used = $ips[$n]['used'] ;
    if($is_used) continue ;
    $issue = $ips[$n]['ip'] ;
    $ips[$n]['used'] = true;
    $check = false ;
  }
  if(strlen($issue > 0)){
    $json = json_encode($ips);
    file_put_contents($conf->ip_addr_file,$json);
    return [true,$issue] ;
  }
  return [false,"no ip addresses available"];  
}

?>
