<?php

include_once 'cs_ipv4.php';

class MT_Account extends MT {

    public function upgrade() {
        $this->data->actionObj = 'before';
        if ($this->delete()) {
            $this->data->actionObj = 'entity';
            $this->insert();
            $this->set_message('service id:' . $this->entity->id . ' was up-down-graded');
        }
    }

    public function move() {
        if ($this->insert()) {
            $this->data->actionObj = 'before';
            $this->delete();
            $this->set_message('service id:' . $this->entity->id . ' was moved');
        }
    }

    public function edit() {
        $id = $this->entity->id;
        $data = $this->data();
        // unset($data->ip);
        if ($this->write($data->device, 'set')) {
            $this->set_message('service id:' . $id . ' was updated');
            $this->save($data->save, 'update');
            return true;
        }
        return false;
    }

    public function insert() {
        $id = $this->entity->id;
        $data = $this->data();
        if ($this->write($data->device, 'add')) {
            $this->set_message('service id:' . $id . ' was added');
            $this->save($data->save);
            return true;
        }
        return false;
    }

    public function suspend() {
        global $conf;
        $id = $this->entity->id;
        $action = 'suspended';
        $data = new stdClass();
        $data->{$this->disableProperty} = $conf->disabled_profile;
        if ($this->data->unsuspendFlag) {
            $action = 'unsuspended';
            $data->{$this->disableProperty} = '';
        }
        if ($this->write($data)) {
            $this->set_message('service id:' . $id . ' was ' . $action);
            return true;
        }
        return false;
    }

    public function delete() {
        $id = $this->{$this->data->actionObj}->id;
        if ($this->write(false, 'remove')) {
            $this->set_message('service id:' . $id . ' was deleted');
            if (in_array($this->data->changeType,['delete','upgrade'])) {
                $this->clear();
            }
            return true;
        }
        return false;
    }

    protected function ip_get($device = false) {
        if (in_array($this->data->changeType, ['edit', 'move'])) {
            $db = new CS_SQLite();
            return $db->get_val($this->entity->id, 'address');
        }
        $ip = new CS_IPv4();
        $addr = $ip->assign($device);
        if (!$addr) {
            $this->set_error('no ip addresses to assign');
            return false;
        }
        return $addr;
    }

    protected function ip_clear($addr) {
        $ip = new CS_IPv4();
        $ip->clear($addr);
    }

    protected function save($data) {
        $db = new CS_SQLite();
        $db->{$this->data->changeType}($data);
    }

    protected function clear() {
        $db = new CS_SQLite();
        $db->delete($this->entity->id);
    }

}
