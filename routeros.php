<?php
require('routeros_api.class.php');
require('ipaddr.php');
$conf = include('config.php');

function ros_del_id($idno){
  global $conf ;
  $filename = $conf->entity_ids_file;
  $file = strtolower(file_get_contents($filename));
  if($file){
    $ids = json_decode($file);
    unset($ids->{$idno});
    $json = json_encode($ids);
    file_put_contents($filename,$json);
  }
}

function ros_recall_site(&$obj){
  global $conf ;
  $filename = $conf->entity_ids_file;
  $file = strtolower(file_get_contents($filename));
  $site = '';
  if($file){
    $ids = json_decode($file);
    if(property_exists($ids,$obj->id))
      $site =  $ids->{$obj->id} ;
  }
  $obj->{$conf->pppoe_site_attr} = $site;
}

function ros_save_site(&$obj){
  global $conf ;
  $filename = $conf->entity_ids_file;
  $ids = new StdClass();
  $file = strtolower(file_get_contents($filename));
  if($file)$ids = json_decode($file);
  $ids->{$obj->id} = $obj->{$conf->pppoe_site_attr};
  $json = json_encode($ids);
  file_put_contents($filename,$json);
  return true ;
}

function ros_resolve_site($site){
  global $conf ;
  $file = strtolower(file_get_contents($conf->gateways_file));
  $gates = json_decode($file);
  $ip = 'no ip address defined for this site';
  $ret = false ;
  if(property_exists($gates,strtolower($site))){
     $ip = $gates->{strtolower($site)};
     $ret = true ;
  }
  return [$ret,$ip];
}


function ros_disconnect(&$obj){
  global $conf ;
  $ret = ros_resolve_site($obj->{$conf->pppoe_site_attr});
  if(!$ret[0]) return $ret ;
  $gate = $ret[1];

  $api = new Routerosapi();
  //$api->debug = true ;
  if ($api->connect($gate, $conf->api_user, $conf->api_pass)) {
    $api->write('/ppp/active/print',false);
    $api->write('?name=' . $obj->{$conf->pppoe_user_attr}) ;
    $read = $api->read();
    $r = array();
    if(sizeof($read) > 0);{
      foreach($read as $conn){
        $api->write('/ppp/active/remove',false);
        $api->write('=.id=' . $conn['.id']);
        $r = $api->read();
      }
    }
    $api->disconnect();
    if(sizeof($read) > 0 && sizeof($r) < 1){
      return true;
    }
  }
  return false ;
}

function ros_ifexists(&$obj){
  global $conf ;
  $id = $obj->{$conf->pppoe_user_attr}  ;
  $ret = ros_resolve_site($obj->{$conf->pppoe_site_attr});
  if(!$ret[0]) return $ret ;
  $gate = $ret[1];

  $api = new Routerosapi();
  //$api->debug = true ;
  if ($api->connect($gate, $conf->api_user, $conf->api_pass)) {
    $api->write('/ppp/secret/print',false);
    $api->write('?name=' . $id);
    $read = $api->read();
    $api->disconnect();
    if(sizeof($read) > 0){
      return true ;
    }
  }
  return false;
}

function ros_edit(&$obj){
  global $conf ;
  $id = $obj->update->id ;
  $name = $obj->update->{$conf->pppoe_user_attr} ;
  $pass = $obj->update->{$conf->pppoe_pass_attr};
  $profile = $obj->update->profile ;
  if($obj->update->status != 1) $profile = 'disabled'; // disable profile if status disabled
  $ret = ros_resolve_site($obj->update->{$conf->pppoe_site_attr});
  if(!$ret[0]) return $ret ;
  $gate = $ret[1];

  $api = new Routerosapi();
  //$api->debug = true ;
  if ($api->connect($gate, $conf->api_user, $conf->api_pass)) {
      $api->write('/ppp/secret/set',false);
      $api->write('=.id=' . $id,false );
      $api->write('=name=' . $name,false);
      $api->write('=comment=' . $id,false);
      $api->write('=password=' . $pass,false);
      $api->write('=profile=' . $profile);
      $read = $api->read();
      $api->disconnect();
      if(sizeof($read) < 1)return [true,'account updated'];
      return [false,$read['!trap'][0]['message']] ;
  }
  return [false,'unable to connect'];
}

function ros_add(&$obj){
  global $conf ;
  $ret = ip_issue();
  if($obj->status != 1)$obj->profile = 'disabled'; //disable profile if status disabled
  if(!$ret[0]) return $ret ;
  $ip = $ret[1];
  $ret = ros_resolve_site($obj->{$conf->pppoe_site_attr});
  if(!$ret[0]) return $ret ;
  $gate = $ret[1];
  $api = new Routerosapi();
  //$api->debug = true ;
  if ($api->connect($gate, $conf->api_user, $conf->api_pass)) {
    $r = $api->comm('/ppp/secret/add',array(
      'name' => $obj->{$conf->pppoe_user_attr},
      'password' => $obj->{$conf->pppoe_pass_attr},
      'profile' => $obj->profile,
      'comment' => $obj->id,
      'remote-address' => $ip,
    ));
    $api->disconnect();
    if(is_string($r)){
      ros_save_site($obj);
      return [true,'account added'];
    }
  }
  return [false,$r['!trap'][0]['message']];
}

function ros_delete(&$obj){
  global $conf ;
  $id = $obj->{$conf->pppoe_user_attr}  ;
  var_dump($id);
  $ret = ros_resolve_site($obj->{$conf->pppoe_site_attr});
  if(!$ret[0]) return $ret ;
  $gate = $ret[1];

  $api = new Routerosapi();
  // $api->debug = true ;
  if ($api->connect($gate, $conf->api_user, $conf->api_pass)) {
    $api->write('/ppp/secret/print',false);
    $api->write('?name=' . $id);
    $user = $api->read();
    if(sizeof($user) < 1) {
      $api->disconnect();
      return [true,'account already deleted'];
    }
    $api->write('/ppp/secret/remove',false);
    $api->write('=.id=' . $id);
    $read = $api->read();
    $api->disconnect() ;
    if(sizeof($read) > 0) return [false,'account not deleted'];
    ros_disconnect($obj);
    ip_release($user[0]['remote-address']);
    ros_del_id($user[0]['comment']);
    return [true,'account has been deleted'];
  }
  return [false,'connection failed'];
}

function ros_enable(&$obj,$bool){
  global $conf ;
  $profile = $obj->profile ;
  $message = 'enabled';
  if(!$bool) $profile = $message = 'disabled';
  $ret = ros_resolve_site($obj->{$conf->pppoe_site_attr});
  if(!$ret[0]) return $ret ;
  $gate = $ret[1];

  $api = new Routerosapi();
  //$api->debug = true ;
  if ($api->connect($gate, $conf->api_user, $conf->api_pass)) {
      $api->write('/ppp/secret/set',false);
      $api->write('=.id=' . $obj->{$conf->pppoe_user_attr},false );
      $api->write('=profile=' . $profile);
      $read = $api->read();
      $api->disconnect();
      if(sizeof($read) < 1){
        ros_disconnect($obj);
        return [true,'account was ' . $message];
      }
      return [false,$read['!trap'][0]['message']];
  }
  return [false,'unable to connect'];
}

?>

