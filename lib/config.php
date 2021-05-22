<?php

return (object) array(
    
    
// mikrotik api user
  'api_user' => 'api', //mikrotik user and pass for api access. create on every gateway
  'api_pass' => '',
  
// file paths
  'devices_file' => 'json/devices.json', // used to map device name to device address
  'ip_addr_file' => 'json/ipaddr.json', // stores used ip addresses
  'ids_file' => 'json/ids.json',  // used to map service id to device name
  'ppp_pool_file' => 'json/ppp_pool.json', //ppp address pool 
  'data_path' => 'data', //path for data store

//disabled profile or address-list
  'disabled_profile' => 'disabled', // name of profile/list for disabled accounts
  
// custom attributes
  'pppoe_user_attr' => 'pppoeUsername',  
  'pppoe_pass_attr' => 'pppoePassword',
  'device_name_attr' => 'deviceName', 
  'mac_addr_attr' => 'dhcpMacAddress',

);




