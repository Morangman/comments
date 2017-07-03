<?php

/**
 * Класс для настроек
 *
 * @author Д.Синепольский
 */
class Config {
    protected $config_file = 'core/config.json';
    
    public function readConfigFile(){
        $string = file_get_contents($this->config_file);
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
        if (!$string){
            throw new Exception($html_msg);
        }
        $json_a = json_decode($string, true);
        if (!$json_a){
            throw new Exception($html_msg);
        }
        return $json_a;
    }
    
    public function getConfigFileName(){
        return $this->config_file;
    }
    
    public function setConfigFileName($newConfigFileName){
        if (!(isset($newConfigFileName) && strlen($newConfigFileName) > 0)){
            throw new Exception('Config:setConfigFileName($newConfigFileName):'
                    .' argument is empty');
        }
        $this->config_file = $newConfigFileName;
        $this->readConfigFile();
    }

}
