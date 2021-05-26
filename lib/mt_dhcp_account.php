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
        $list = $conf->active_list;
        if ($this->entity->status != 1) {
            $list = $conf->disabled_profile;
        }
        $data = (object) array(
                    'save' => (object) array(
                        'id' => $this->entity->id,
                        'planId' => $this->entity->servicePlanId,
                        'clientId' => $this->entity->clientId,
                        'address' => $ip,
                        'status' => $this->entity->status,
                        'device' => $this->entity->{$conf->device_name_attr},
                    ),
                    'device' => (object) array(
                        'address' => $ip,
                        'mac-address' => $this->entity->{$conf->mac_addr_attr},
                        'insert-queue-before' => 'bottom',
                        'rate-limit' => $this->entity->downloadSpeed . "M/"
                        . $this->entity->uploadSpeed . "M",
                        'address-lists' => $list,
                        'comment' => $this->entity->id.','.$this->data->clientName,
                    ),
        );
        if (in_array($this->data->changeType,['edit','move'])) {
            unset($data->save->ip);
        }
        return $data ;
    }

}
