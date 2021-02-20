<?php
header('content-type: application/json');
require('routeros.php');
$conf = include('config.php');

function http_response($bool,$message){
  $status = 'success';
  if(!$bool){ // failed response
    header('X-API-Response: 406', true, 406);
    $status = 'failed';
  }
  return json_encode(
     array(
      'status' => $status,
      'message' => $message, ));
}

// get json request
$body = file_get_contents('php://input');

//body is empty
if(is_null($body)){
 exit(http_response(false,'request has no body'));
}
$data = json_decode($body);
// request object not a service
if(!in_array($data->entity,array('service','webhook'))){
  exit(http_response(false,'request object is not a service'));
}
// test webhook
if($data->entity == 'webhook'){
  exit (http_response(true,'test webhook acknowledged'));
}
// no custom attributes in object
if(!$data->extraData->entity->attributes)
	exit(http_response(false,'object has no pppoe attributes'));

//configure custom attributes
$pppoe = include('pppoe.php');
$type = $data->changeType ;

$pppoe->update->profile = $data->extraData->entity->servicePlanName ;
$pppoe->update->status = $data->extraData->entity->status ;
foreach($data->extraData->entity->attributes as $attribute){
  $pppoe->update->{$attribute->key} = $attribute->value ;
}
//empty username
if(!$pppoe->update->{$conf->pppoe_user_attr})
  //strlen(!$pppoe->update->{$conf->pppoe_user_attr}) < 1)
    exit(http_response(false,'request has no pppoe username'));

if($data->extraData->entityBeforeEdit){
  foreach($data->extraData->entityBeforeEdit->attributes as $attribute){
    $pppoe->last->{$attribute->key} = $attribute->value ;
  }
}
unset($data); //we are done with this 

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
  if($stat[0])exit(http_response(true,$stat[1]));
    exit(http_response(false,$stat[1]));
}


if($type == 'insert'){
  $stat = ros_add($pppoe->update);
  if($stat[0])exit(
    http_response(true,$stat[1]));
  exit(http_response(false,$stat[1]));
}

if($type == 'edit'){
  $stat = array();
 if($pppoe->update->{$conf->pppoe_site_attr} !=
    $pppoe->last->{$conf->pppoe_site_attr}){ // site has changed
      $stat = ros_add($pppoe->update); // move to new site
      $stat = ros_delete($pppoe->last); //delete from old site
  }else{
    $stat = ros_edit($pppoe) ; //normal edit
  }
  if($stat[0]) exit(http_response(true,$stat[1]));
  exit(http_response(false,$stat[1]));
}

?>

