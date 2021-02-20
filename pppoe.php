<?php

function pppoe_object(){
  $conf = include('config.php');
  $pppoe = new StdClass();
  $pppoe->update = new StdClass() ;
  $pppoe->last = new StdClass() ;

  foreach([$pppoe->update,$pppoe->last] as $obj){
    $obj->profile = '';
    $obj->status = -1 ;
    $obj->{$conf->pppoe_user_attr} = '' ;
    $obj->{$conf->pppoe_pass_attr} = '' ;
    $obj->{$conf->pppoe_site_attr} = '' ;  
  }
  return $pppoe ;
}

return(pppoe_object());
