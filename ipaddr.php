<?php
$conf = include('config.php');

function ip_release($ip){
  global $conf ;
  $file = file_get_contents($conf->ip_addr_file);
  if(!$file) return false ;
  $ips = json_decode($file);
  foreach($ips as $thisone){
    $address = $thisone->ip ;
    if($ip != $address) continue ;
    $thisone->used = false;
    break ;
  }
  $json = json_encode($ips);
  file_put_contents($conf->ip_addr_file,$json);
  return true ;
}

function ip_issue(){
  global $conf ;
  $file = file_get_contents($conf->ip_addr_file);
  $ips = json_decode($file);
  $check = true ;
  $i = 0 ;
  $issue = '';
  foreach($ips as $thisone){
    if($thisone->used) continue ;
    $thisone->used = true ;
    $issue = $thisone->ip ;
    break ;
  }
  if(strlen($issue > 0)){
    $json = json_encode($ips);
    file_put_contents($conf->ip_addr_file,$json);
    return [true,$issue] ;
  }
  return [false,"no ip addresses available"];
}

?>
