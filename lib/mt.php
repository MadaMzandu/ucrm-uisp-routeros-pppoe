<?php

include_once 'routeros_api.class.php';
include_once 'cs_file.php';
include_once 'cs_sqlite.php';
$conf = include 'config.php';

class MT {

    protected $data;
    protected $entity;
    protected $before;
    protected $device;
    protected $status;
    protected $result;
    protected $path;
    protected $disableProperty = 'profile';

    public function __construct(&$data) {
        $this->data = $data;
        $this->entity = &$this->data->extraData->entity;
        $this->before = &$this->data->extraData->entityBeforeEdit;
        $this->init();
    }
    
    public function status(){
        return $this->status ;
    }

    protected function connect() {
        global $conf;
        $this->get_device();
        $ip = $this->device->ip;
        $api = new Routerosapi();
        // $api->debug = true;
        if ($api->connect($ip, $conf->api_user, $conf->api_pass)) {
            return $api;
        }
        $this->set_error('could not connect to device');
        return false;
    }

    protected function get_device() {
        global $conf; 
        $name = $this->{$this->data->actionObj}->{$conf->device_name_attr};
        $file = new CS_File('devices');
        $devices = $file->read();
        if (property_exists($devices, strtolower($name))) {
            $this->device = $devices->{strtolower($name)};
            return true;
        }
        $this->set_error('device not listed in config file');
        return false;
    }

    protected function init() {
        $this->status = (object) array();
        $this->status->error = false;
    }

    protected function set_message($msg) {
        $this->status->error = false;
        $this->status->message = $msg;
    }

    protected function set_error($msg, $obj = false) {
        $this->status->error = true;
        if ($obj) {
            $this->status->message = $this->result['!trap'][0]['message'];
        } else {
            $this->status->message = $msg;
        }
    }

    protected function read() {
        global $conf;
        $this->get_device(
                $this->entity->
                {$conf->device_name_attr});
        $api = $this->connect();
        if (!$api) {
            return false;
        }
        $api->write($this->path . 'print');
        $this->result = $api->read();
        $api->disconnect();
        return true;
    }

    protected function write($data, $action = 'set') {
        if (!$this->exists() && $action != 'add') {
            return false;
        }
        $api = $this->connect();
        if (!$api) {
            return false;
        }
        $api->write($this->path . $action, false);
        if ($data) {
            foreach (array_keys((array) $data) as $key) {
                $api->write('=' . $key . '=' . $data->$key, false);
            }
        }
        if ($action != 'add') {
            $api->write('=.id=' . $this->result[0]['.id'], false);
        }
        $api->write(';'); // trailing semi-colon works
        $this->result = $api->read();
        $api->disconnect();
        if (!$this->result || is_string($this->result)) { //don't care what's inside the string?
            return true;
        }
        $this->set_error('action failed', true);
        return false;
    }

    protected function exists() {
        return $this->get();
    }

    protected function get() {
        $api = $this->connect();
        if (!$api) {
            return false;
        }
        $id = $this->{$this->data->actionObj}->id;
        $api->write($this->path . 'print', false);
        $api->write('?comment=' . $id);
        $this->result = $api->read();
        $api->disconnect();
        if (!$this->result) {
            $this->set_message('service id:' . $id . ' was not found');
            return false;
        }
        $this->set_message('service id:' . $id . ' was found');
        return true;
    }
    
}
