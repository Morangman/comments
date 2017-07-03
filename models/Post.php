<?php

require_once 'core/Connector.php';
require_once 'models/User.php';
require_once 'models/Comment.php';


/**
 * Модель для сообщений
 *
 * @author Д.Синепольский
 */
class Post {
    /**
     *
     * @var Connector 
     */
    protected static $connObj = NULL;
    public $id;
    public $created_at;
    public $text_comment;
    public $user_id;
    public $comments;
    public $terminal;

    protected static function setConnector(){
        if (!isset(Post::$connObj)){
            Post::$connObj = new Connector();
        }
    }
    
    function __construct() {
        Post::setConnector();
    }
   
    public function getComments(){
        return Comment::getAll($this->id);
    }
    
    public function getUser(){
        return User::getOne($this->user_id);
    }
            
    public static function getAll(){
        Post::setConnector();
        $result = Post::$connObj->execQuery("SELECT * FROM comments "
                ."WHERE parent_id IS NULL "
                ."ORDER BY created_at DESC");
        $ret = [];
        $thisName = get_called_class();
        if (count($result) > 0){
            for ($i = 0; $i < count($result); $i++){
                $obj = new $thisName();
                $obj->id = $result[$i]['id'];
                $obj->created_at = $result[$i]['created_at'];
                $obj->text_comment = $result[$i]['text_comment'];
                $obj->user_id = $result[$i]['user_id'];
                $obj->terminal = $result[$i]['terminal'];
                $obj->comments = $obj->getComments();
                $ret []= $obj;
            }
        }
        return $ret;
    }
    
    public static function getOne($id=0){
        Post::setConnector();
        $result = Post::$connObj->execQuery("SELECT * FROM comments WHERE 1=1"
                .((intval($id)>0) ? " AND id=".intval($id)." " : " ")
                ."AND parent_id IS NULL "
                . "ORDER BY created_at DESC LIMIT 1");
        $obj = NULL;
        $thisName = get_called_class();
        if (count($result) === 1){
            $obj = new $thisName();
            $obj->id = $result[0]['id'];
            $obj->created_at = $result[0]['created_at'];
            $obj->text_comment = $result[0]['text_comment'];
            $obj->user_id = $result[0]['user_id'];
            $obj->terminal = $result[0]['terminal'];
            $obj->comments = $obj->getComments();
        }
        return $obj;
    }


    public function save(){
        $query = "";
        if ($this->id > 0){
            $query = "UPDATE comments SET ";
            $query .= "created_at='".mysqli_escape_string(Post::$connObj->getLink(), $this->created_at)."', ";
            $query .= "text_comment='"
                    .htmlspecialchars(mysqli_escape_string(Post::$connObj->getLink(), $this->text_comment))."', ";
            $query .= "user_id=".intval($this->user_id).", ";
            $query .= "terminal=".intval($this->terminal)." ";
            $query .= "WHERE id=".intval($this->id);
        } else {
            $query = "INSERT INTO comments(text_comment,user_id) VALUES(";
            $query .= "'".htmlspecialchars(mysqli_escape_string(Post::$connObj->getLink(), $this->text_comment))."',";
            $query .= intval($this->user_id);
            $query .= ")";
        }
        if (!Post::$connObj->execQuery($query)){
            throw new Exception('Problem with query: '.$query);
        }
        return true;
    }
    
    public function validate(){
        if (strlen(trim($this->text_comment))==0){
            throw new Exception('Недопустимо сохранять пустую запись');
        }
    }
    
    public function delete(){
        $query = "DELETE FROM comments WHERE id=".intval($this->id);
        if (!Post::$connObj->execQuery($query)){
            throw new Exception('Problem with query: '.$query);
        }
        return true;
    }
}

/*
$obj = Post::getOne(4);
try{
    $obj->delete();
    echo 'OK';
}catch(Exception $exc){
    echo $exc->getMessage();
}
/*
$obj->user_id = 1;
$obj->text_comment = "Ого!!!....";
try{
    $obj->save();
    echo 'Сохранено!';
} catch(Exception $exc){
    echo $exc->getMessage();
};
 */
 
?>
