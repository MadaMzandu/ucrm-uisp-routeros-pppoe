<?php


class CS_SQLite{
    
    private $path ;
    private $db ;
    private $data ;
    private $table ;
    private $id ;
    
    public function __construct() {
        global $conf ;
        $this->path = $conf->data_path . '/.data.db';
        $this->db = new SQLite3($this->path);
    }
    
    public function insert($data,$table='services'){
        $this->data = $data ;
        $this->table = $table ;
        $this->db->exec($this->prep_insert());
    }
    
    public function upgrade($data,$table='services'){
        $this->insert($data,$table);
    }
    
    public function exists($col,$val,$table='services'){
        $sql = 'select id from '.$table.
                ' where '.$col."='".$val."'" ;
        if($this->db->querySingle($sql)){
            return true ;
        }
        return false ;
    }
    
    public function get_val($id,$col,$table='services') {
        $sql = 'select '.$col." from ".$table." where id=".$id;
        return $this->db->querySingle($sql) ;
    }
    
    public function delete($id,$table='services'){
        $sql='delete from '.$table." where id=".$id;
        $this->db->exec($sql);
    }
    public function delete_all($table='services'){
        $sql='delete from '.$table ;
        $this->db->exec($sql);
    }
    
    public function edit($data,$table='services'){
        $this->id = $data->id ;
        unset($data->id);
        $this->data = $data ;
        $this->table = $table;
        $this->db->exec($this->prep_update());
    }
    
    public function move($data,$table='services'){
        $this->edit($data,$table);
    }
    
    private function prep_update(){
        $sql = 'update '.$this->table." set ";
         $keys = array_keys((array)$this->data);
         $fields ='';
         foreach($keys as $key){
             $fields .= $key . "='" . $this->data->{$key} . "',";
         }
         return $sql. substr($fields, 0, -1)." where id=".$this->id;
    }
    
    private function prep_insert(){
        $sql = 'insert into '. $this->table." (";
        $keys = array_keys((array)$this->data);
        $vals = array();
        foreach($keys as $key){            
            $vals[] = "'". $this->data->{$key} ."'" ;
        }
        return $sql . implode(',',$keys).") values (".
                implode(',',$vals). ")" ;
    }
    
}