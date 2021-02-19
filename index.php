<?php
header('content-type: application/json');
require('routeros.php');

function http_failed($message){
  header('X-API-Response: 406', true, 406);
  return json_encode(
     array(
      'status' => 'failed',
      'message' => $message, ));
}

function http_success ($message){
  return json_encode(
     array(
      'status' => 'ok',
      'message' => $message, ));
}


// get post and check sanity
$body = file_get_contents('php://input');

//body is empty
if(is_null($body)){
 exit(http_failed('request has no body'));
}
$data = json_decode($body);
// request object not a service
if(!in_array($data->entity,array('service','webhook'))){
  exit(http_failed('request object is not a service'));
}
// test webhook
if($data->entity == 'webhook'){
  exit (http_success('test webhook acknowledged'));
}

//grab custom attributes
$pppoe = new StdClass();
$pppoe->update = new StdClass() ;
$pppoe->last = new StdClass() ;
$type = $data->changeType ;

$pppoe->update->profile = $data->extraData->entity->servicePlanName ;
$pppoe->update->status = $data->extraData->entity->status ;
foreach($data->extraData->entity->attributes as $attribute){
  $pppoe->update->{$attribute->key} = $attribute->value ;
}

if($data->extraData->entityBeforeEdit){
  foreach($data->extraData->entityBeforeEdit->attributes as $attribute){
    $pppoe->last->{$attribute->key} = $attribute->value ;
  }
}

//suspend, unsuspend and delete
if(!in_array($type,array('insert','edit')))
{
  $stat = [false,'request action not supported'];
  if($type == 'end' || $type == 'archive'){
    $stat = ros_delete($pppoe->update);    
  }
  if($type == 'suspend'){
    $stat = ros_enable($pppoe->update,false);
  }
  if($type == 'unsuspend' || $type == 'activate'){
    $stat = ros_enable($pppoe->update,true);
  }
  if($stat[0])exit(http_success($stat[1]));
    exit(http_failed($stat[1]));
}


if($type == 'insert'){
  $stat = ros_add($pppoe->update);
  if($stat[0])exit(
    http_success($stat[1]));
  exit(http_failed($stat[1]));
}

if($type == 'edit'){
  $stat = array();
 if($pppoe->update->{$conf->pppoe_site_attr} !=
    $pppoe->last->{$conf->pppoe_site_attr}){ // site has changed
      $stat = ros_add($pppoe->update); // move to new site
      if($pppoe->update->status != 1){
        $stat = ros_enable($pppoe->update,false); // disable if suspended
      }      
      $stat = ros_delete($pppoe->last); //delete from old site
  }else{
    $stat = ros_edit($pppoe) ; //normal edit
  }
  if($stat[0]) exit(http_success($stat[1]));
  exit(http_failed($stat[1]));
}

?>

