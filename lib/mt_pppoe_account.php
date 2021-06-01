<?php

include_once 'mt_account.php';

class MT_PPPoE_Account extends MT_Account {

    public function __construct(&$data) {
        parent::__construct($data);
        $this->path = '/ppp/secret/';
    }

    public function delete() {
        $id = $this->entity->id;
        if (parent::delete()) {
            $this->disconnect();
            $this->set_message('service id:' . $id . ' was deleted');
            return true;
        }
        return false;
    }

    public function move() {
        $id = $this->entity->id;
        if (parent::move()) {
            $this->data->actionObj = 'before';
            $this->disconnect();
            $this->set_message('service id:' . $id . ' was updated');
            return true;
        }
        return false;
    }

    public function edit() {
        $id = $this->entity->id;
        if (parent::edit()) {
            $this->disconnect();
            $this->set_message('service id:' . $id . ' was updated');
            return true;
        }
        return false;
    }

    private function disconnect() {
        $api = $this->connect();
        if ($api) {
            $api->write('/ppp/active/print', false);
            $api->write('?comment');
            $conns = $this->find($api->read());
            foreach ($conns as $conn) {
                $api->write('/ppp/active/remove', false);
                $api->write('=.id=' . $conn['.id']);
                $api->read();
            }
            $api->disconnect();
        }
    }

    public function suspend() {
        global $conf;
        $data = new stdClass();
        $id = $this->entity->id;
        $action = 'suspended';
        $data->{$this->disableProperty} = $conf->disabled_profile;
        if ($this->data->unsuspendFlag) {
            $action = 'unsuspended';
            $data->{$this->disableProperty} = $this->entity->servicePlanName;
        }
        if ($this->write($data)) {
            $this->disconnect();
            $this->set_message('service id:' . $id . ' was ' . $action);
            return true;
        }
        return false;
    }

    protected function data() {
        $ip = $this->ip_get();
        if (!$ip) {
            return false;
        }
        $data = (object) array(
                    'save' => $this->save_data($ip),
                    'device' => $this->account_data($ip),
        );
        if (in_array($this->data->changeType, ['edit', 'move'])) {
            unset($data->save->ip);
        }
        return $data;
    }

    private function account_data($ip) {
        global $conf;
        $profile = $this->entity->servicePlanName;
        if ($this->entity->status != 1) {
            $profile = $conf->disabled_profile;
        }
        return (object) array(
                    'remote-address' => $ip,
                    'name' => $this->entity->{$conf->pppoe_user_attr},
                    'password' => $this->entity->{$conf->pppoe_pass_attr},
                    'profile' => $profile,
                    'comment' => $this->entity->id . ',' . $this->data->clientName,
        );
    }

}
