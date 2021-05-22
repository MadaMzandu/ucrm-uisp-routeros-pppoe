<?php

include_once 'cs_sqlite.php';
include_once 'cs_file.php';

include_once 'mt.php';
include_once 'mt_pppoe_account.php';
include_once 'mt_dhcp_account.php';

class CS_Router {

    private $data;
    private $entity;
    private $before;
    private $status;

    public function __construct(&$data) {
        $this->data = $data;
        $this->entity = &$this->data->extraData->entity;
        $this->before = &$this->data->extraData->entityBeforeEdit;
        $this->status = new stdClass();
    }

    public function route() {
        // validate data
        if (!$this->validate()) {
            return;
        }
        // sanitize data
        $this->sanitize();

        //select module
        $module = 'MT_' . $this->module() . '_Account';

        // execute
        $service = new $module($this->data);
        $action = $this->data->changeType;
        $service->$action();
        $this->status = $service->status();
    }

    private function validate() {
        $data = &$this->data;
        if ($data->entity != 'service') {
            $this->set_message('ok');
            return false;
        }
        if (!$this->check_attrbs()) {
            return false;
        }
        return true;
    }

    private function check_attrbs() {
        global $conf;
        $attrbs = $this->get_attrbs();
        if(!in_array($this->data->changeType,['insert','edit','end',
            'suspend','unsuspend'])){
            $this->set_message('ok');
            return false ;
        }
        if (!isset($attrbs->{$conf->device_name_attr})) {
            $this->set_error('network device property not configured');
            return false;
        }
        if (!isset($attrbs->{$conf->pppoe_user_attr}) &&
                !isset($attrbs->{$conf->mac_addr_attr})) {
            $this->set_error('ppp username or mac address not configured');
            return false;
        }
        return true;
    }

    private function get_attrbs() {
        $data = new stdClass();
        $attrbs = $this->data->extraData->entity->attributes;
        foreach ($attrbs as $attrb) {
            $data->{$attrb->key} = $attrb->value;
        }
        return $data;
    }

    private function module() {
        global $conf;
        $module = 'PPPoE';
        if (filter_var($this->data->extraData->entity->{$conf->mac_addr_attr},
                        FILTER_VALIDATE_MAC)) { //mac address is valid
            $module = 'DHCP';
        }
        return $module;
    }

    private function sanitize() {
        $this->set_custom_attr();
        if (in_array($this->data->changeType, ['end'])) {
            $this->data->changeType = 'delete';
        }
        if (in_array($this->data->changeType, ['suspend', 'unsuspend'])) {
            $this->data->unsuspendFlag = false;
            if ($this->data->changeType == 'unsuspend') {
                $this->data->unsuspendFlag = true;
                $this->data->changeType = 'suspend';
            }
        }
        if (in_array($this->data->changeType, ['edit'])) {
            if ($this->check_exists()) {
                $this->check_device_move();
            }
        }
    }

    private function check_exists() {
        $db = new CS_SQLite();
        if (!$db->exists('id', $this->data->entityId)) {
            $this->data->changeType = 'insert';
            return false;
        }
        return true;
    }

    private function check_device_move() {
        global $conf;
        $device = $this->get_device();
        if ($this->entity->{$conf->device_name_attr} != $device) {
            $this->before->{$conf->device_name_attr} = $device;
            $this->data->changeType = 'move';
        }
    }

    private function get_device() {
        $db = new CS_SQLite();
        return $db->get_val($this->data->entityId, 'device');
    }

    private function set_custom_attr() {
        $this->data->actionObj = 'entity';
        foreach (['entity', 'entityBeforeEdit'] as $obj) {
            if (!$this->data->extraData->$obj ||
                    !$this->data->extraData->$obj->attributes) {
                continue;
            }
            foreach ($this->data->extraData->$obj->attributes as $attr) {
                $this->data->extraData->$obj->{$attr->key} = $attr->value;
            }
        }
    }

    public function http_response() {
        $status = 'success';
        if ($this->status->error) { // failed response
            header('X-API-Response: 406', true, 406);
            $status = 'failed';
        }
        return json_encode(
                array(
                    'status' => $status,
                    'message' => $this->status->message,));
    }

    private function set_message($msg) {
        $this->status->error = false;
        $this->status->message = $msg;
    }

    private function set_error($msg, $obj = false) {
        $this->status->error = true;
        if ($obj) {
            $this->status->message = $this->result['!trap'][0]['message'];
        } else {
            $this->status->message = $msg;
        }
    }

}
