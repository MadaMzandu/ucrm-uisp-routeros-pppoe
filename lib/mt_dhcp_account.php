<?php

include_once 'mt_account.php';

class MT_DHCP_Account extends MT_Account {

    public function __construct(&$data) {
        parent::__construct($data);
        $this->path = '/ip/dhcp-server/lease/';
        $this->disableProperty = 'address-lists';
    }
    
    protected function data() {
        global $conf;
        $ip = $this->ip_get($this->entity->{$conf->device_name_attr});
        if (!$ip) {
            return false;
        }
        $data = (object) array(
            'save' => $this->save_data($ip),
            'device' => $this->lease_data($ip),
        );
        if (in_array($this->data->changeType, ['edit', 'move'])) {
            unset($data->save->ip);
        }
        return $data;
    }
    
    protected function lease_data($ip) {
        global $conf ;
        $ul = $this->entity->uploadSpeed."M";
        $dl = $this->entity->downloadSpeed."M";
        $list = $conf->active_list;
        if ($this->entity->status != 1) {
            $list = $conf->disabled_profile;
        }
        return (object) array(
                    'address' => $ip,
                    'mac-address' => $this->entity->{$conf->mac_addr_attr},
                    'insert-queue-before' => 'bottom',
                    'address-lists' => $list,
                    'rate-limit' => $ul.'/'.$dl,
                    'comment' => $this->entity->id . ',' . $this->data->clientName,
        );
    }


}
