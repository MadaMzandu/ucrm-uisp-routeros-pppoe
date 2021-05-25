<?php

$conf = include 'config.php';

class CS_File {

    private $paths;
    private $path;

    public function __construct($type = 'devices') {
        $this->init($type);
    }

    public function read() {
        $data = json_decode(strtolower(file_get_contents($this->path)));
        if ($data) {
            return $data;
        }
        return false;
    }
    
    public function save(&$data,$mode=0){
        file_put_contents($this->path, json_encode($data),$mode) ;
    }
    
    public function clear(){
        $this->save([]);
    }

    private function init($type) {
        $this->paths();
        $this->set_path($type);
    }

    private function set_path($type) {
        $this->path = $this->paths->{strtolower($type)};
    }

    private function paths() {
        global $conf;
        $this->paths = (object) array(
                    'ppp' => $conf->ppp_pool_file,
                    'devices' => $conf->devices_file,
                    'dhcp_excl' => $conf->dhcp_excl_file,
        );
    }

}
