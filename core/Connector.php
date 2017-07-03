<?php

require_once 'core/Config.php';
/**
 * Класс для соединения с БД
 *
 * @author Д.Синепольский
 */
class Connector extends Config {
    private $link = null;
    
    public function getLink(){
        return $this->link;
    }
    
    
    
    public function connect($host, $user, $password, $database){
        if (!(isset($host) && strlen($host)>0
                && isset($user) && strlen($user)>0
                && isset($password) && strlen($password)>0
                && isset($database) && strlen($database)>0
                )){
            throw new Exception('Connector:connect($host, $user, $password, $database) - one or few arguments are empty');
        }
        $this->link = @mysqli_connect($host, $user, $password, $database);
        if (!$this->link){
            throw new Exception('Connector:connect($host, $user, $password, $database) - can\'t connect with parameters'
            . " {host: '$host', user: '$user', password: FORBIDDEN_INFO, database: '$database'}");
        }
        mysqli_query($this->link, "SET NAMES 'utf8' ");
    }
    
   function __construct() {
       $json_a = $this->readConfigFile();
       if ((!isset($json_a['host'])) 
           || (!isset($json_a['username']))
           || (!isset($json_a['password'])) 
           || (!isset($json_a['database'])) ){
        $html_msg = "<p>Для работы WEB-ресурса необходим файл в формате JSON : ".$this->config_file." с содержимым:</p>";
        $html_msg .= "<pre>{
    \"host\": \"Имя или адрес хоста СУБД MySQL\",
    \"username\": \"Имя пользователя для подключения к БД\",
    \"password\": \"Пароль\",
    \"database\": \"Имя БД\",
    \"client_id\": \"Идентификатор приложения Facebook для авторизации\",
    \"client_secret\": \"Строка-ключ приложения Facebook для авторизации\"
}
</pre>";

         throw new Exception($html_msg);
       }
       $this->connect($json_a['host'], $json_a['username'], $json_a['password'], $json_a['database']);
   }
   
   public function closeConnection(){
       if ($this->link){
         mysqli_close($this->link);
       }
       $this->link = NULL;
   }
   
   function __destruct() {
       $this->closeConnection();
   }
   
   public function execQuery($query){
       if (!isset($query) || strlen($query) == 0){
           return NULL;
       }
       $result = mysqli_query($this->link, $query);
       if (!$result){
           $err = mysqli_error($this->link);
           throw new Exception($err);
       }
       if ($result === TRUE){
           return $result; 
       }
       $data_row = NULL;
       $result_array = [];
       while (($data_row = mysqli_fetch_assoc($result)) !== NULL){
           $result_array []= $data_row;
       }
       return $result_array;
   }
}


?>
