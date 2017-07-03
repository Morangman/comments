<?php

require_once 'core/Connector.php';

/**
 * Класс для модели пользователя
 *
 * @author Д.Синепольский
 */
class User {
    protected static $connObj = NULL;
    public $id;
    public $facebook_name;
    public $facebook_id;
    
    protected static function setConnector(){
        if (!isset(User::$connObj)){
            User::$connObj = new Connector();
        }
    }
    
    function __construct() {
        User::setConnector();
    }
    
    public static function getAll(){
        User::setConnector();
        $result = User::$connObj->execQuery("SELECT * FROM users "
                ."ORDER BY facebook_name");
        $ret = [];
        $thisName = get_called_class();
        if (count($result) > 0){
            for ($i = 0; $i < count($result); $i++){
                $obj = new $thisName();
                $obj->id = $result[$i]['id'];
                $obj->facebook_name = $result[$i]['facebook_name'];
                $obj->facebook_id = $result[$i]['facebook_id'];
                $ret []= $obj;
            }
        }
        return $ret;
    }
    
    public static function getOne($id=0,$facebook_id=""){
        User::setConnector();
        $f_id = mysqli_escape_string(self::$connObj->getLink(), $facebook_id);
        $result = User::$connObj->execQuery("SELECT * FROM users WHERE 1=1 "
                .((intval($id)>0) ? "AND id=".intval($id)." " : " ") 
                .((strlen($facebook_id)>0) ? "AND facebook_id='".$f_id."' " : " ") 
                . "ORDER BY facebook_name LIMIT 1");
        $obj = NULL;
        $thisName = get_called_class();
        if (count($result) === 1){
            $obj = new $thisName();
            $obj->id = $result[0]['id'];
            $obj->facebook_name = $result[0]['facebook_name'];
            $obj->facebook_id = $result[0]['facebook_id'];
        }
        return $obj;
    }


    public function save(){
        $query = "";
        if ($this->id > 0){
            $query = "UPDATE comments SET ";
            $query .= "facebook_name='"
                    .htmlspecialchars(mysqli_escape_string(User::$connObj->getLink(), $this->facebook_name))."', ";
            $query .= "facebook_id='"
                    .htmlspecialchars(mysqli_escape_string(User::$connObj->getLink(), $this->facebook_id))."' ";
            $query .= "WHERE id=".intval($this->id);
        } else {
            $query = "INSERT INTO users(facebook_name,facebook_id) VALUES(";
            $query .= "'".htmlspecialchars(mysqli_escape_string(User::$connObj->getLink(), $this->facebook_name))."',";
            $query .= "'".htmlspecialchars(mysqli_escape_string(User::$connObj->getLink(), $this->facebook_id))."'";
            $query .= ")";
        }
        if (!User::$connObj->execQuery($query)){
            throw new Exception('Problem with query: '.$query);
        }
        return true;
    }
    
    public function delete(){
        $query = "DELETE FROM users WHERE id=".intval($this->id);
        if (!User::$connObj->execQuery($query)){
            throw new Exception('Problem with query: '.$query);
        }
        return true;
    }
    
    
}
