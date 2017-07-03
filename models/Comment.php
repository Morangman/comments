<?php

require_once 'models/Post.php';

/**
 * Класс модели комментариев - наследник Post
 *
 * @author Д. Синепольский
 */
class Comment extends Post {
    public $parent_id;
    
    public static function getAll($parent_id){
        Post::setConnector();
        $result = Post::$connObj->execQuery("SELECT * FROM comments "
                ."WHERE parent_id = ".intval($parent_id)." "
                ."ORDER BY created_at ASC");
        $ret = [];
        $thisName = get_called_class();
        if (count($result) > 0){
            for ($i = 0; $i < count($result); $i++){
                $obj = new $thisName();
                $obj->id = $result[$i]['id'];
                $obj->created_at = $result[$i]['created_at'];
                $obj->text_comment = $result[$i]['text_comment'];
                $obj->user_id = $result[$i]['user_id'];
                $obj->parent_id = $result[$i]['parent_id'];
                $obj->terminal = $result[$i]['terminal'];
                if (!$obj->terminal){
                    $obj->comments = Comment::getAll($obj->id);
                }
                $ret []= $obj;
            }
        }
        return $ret;
    }

    public static function getOne($parent_id=0,$id=0){
        Comment::setConnector();
        $result = Post::$connObj->execQuery("SELECT * FROM comments "
                ."WHERE parent_id".(($parent_id > 0)? " = ".intval($parent_id) : " > 0")." "
                .((intval($id)>0)? "AND id=".intval($id)." ":" ")
                ."ORDER BY created_at ".(($id < 0)? "DESC": "")." LIMIT 1");
        $thisName = get_called_class();
        $obj = NULL; $i=0;
        if (count($result) === 1){
            $obj = new $thisName();
            $obj->id = $result[$i]['id'];
            $obj->created_at = $result[$i]['created_at'];
            $obj->text_comment = $result[$i]['text_comment'];
            $obj->user_id = $result[$i]['user_id'];
            $obj->parent_id = $result[$i]['parent_id'];
            $obj->terminal = $result[$i]['terminal'];
            if (!$obj->terminal){
                $obj->comments = Comment::getAll($obj->id);
            }
        }
        return $obj;
    }
        
    public function getParent(){
        $obj = Comment::getOne(0,$this->parent_id);
        if ($obj == NULL){
            $obj = Post::getOne($this->parent_id);
        }
        return $obj;
    }
    
    public function save(){
        $query = "";
        if ($this->parent_id > 0){
            $obj = $this->getParent();
            $obj->terminal = 0;
            $obj->save();
        }
        if ($this->id > 0){
            $query = "UPDATE comments SET ";
            $query .= "created_at='".mysqli_escape_string(Comment::$connObj->getLink(), $this->created_at)."', ";
            $query .= "text_comment='"
                    .htmlspecialchars(mysqli_escape_string(Comment::$connObj->getLink(), $this->text_comment))."', ";
            $query .= "parent_id=".intval($this->parent_id).", ";
            $query .= "terminal=".intval($this->terminal).", ";
            $query .= "user_id=".intval($this->user_id)." ";
            $query .= "WHERE id=".intval($this->id);
        } else {
            $query = "INSERT INTO comments(text_comment,user_id,parent_id) VALUES(";
            $query .= "'".htmlspecialchars(mysqli_escape_string(Comment::$connObj->getLink(), $this->text_comment))."',";
            $query .= intval($this->user_id).",";
            $query .= intval($this->parent_id);
            $query .= ")";
        }
        if (!Comment::$connObj->execQuery($query)){
            throw new Exception('Problem with query: '.$query);
        }
        return true;
    }
    
    public function delete(){
        $obj = Post::getOne($this->parent_id);
        if (!$obj){
            $obj = Comment::getOne(0,$this->parent_id);
        }
        if ($obj && (count($obj->comments) === 1)){
            $obj->terminal = 1;
            $obj->save();
        }
        parent::delete();
    }
    
}

/*
$obj = new Comment();
$obj->parent_id = 5;
$obj->user_id=1;
$obj->text_comment = 'Комментарий на комментарий....';
try {
    $obj->save();
    echo 'OK!';
} catch(Exception $ex){
    echo $ex->getMessage();
}
 * 
 */

