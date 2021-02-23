<?php

return (object) array(

  'api_user' => 'api', //mikrotik user and pass for api access. create on every gateway
  'api_pass' => '',
  'gateways_file' => 'json/gateways.json', // used to map site name to gateway address
  'ip_addr_file' => 'json/ipaddr.json', // ip address pool
  'entity_ids_file' => 'json/ids.json',  //used map service id to gateway site
  'pppoe_disabled_profile' => 'disabled', // name of profile for disabled accounts
  'pppoe_user_attr' => 'pppoeUsername',  
  'pppoe_pass_attr' => 'pppoePassword',
  'pppoe_site_attr' => 'pppoeSiteName', 

);


?>

