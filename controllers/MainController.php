<?php

require_once 'models/Post.php';

/**
 * Главный контроллер
 *
 * @author Д.Синепольский
 */
class MainController {
    public $layout = "views/layouts/main.php";
    public $user_id = 0;
    public $auth_params = [];
    public $auth_url = "";
    protected $client_secret = "";
    
    public function getViewContents($filename, $params) {
        if (is_file($filename)) {
            ob_start();
            include $filename;
            return ob_get_clean();
        }
        return false;
    }
    
    public function render($viewFile, $params){
        include $this->layout;
    }
    
    public function actionIndex(){
        if ($this->user_id && isset($_COOKIE['user_id'])){
            $this->actionMessages();
        } else {
            $this->render("views/loginpage.php", []);        
        }
    }
	
	public function actionMessages(){
        try{
            $posts = Post::getAll();
            $this->render("views/index.php", ['posts' => $posts]);        
        } catch (Exception $exc){
            $this->render("views/error.php", ['error' => $exc->getMessage()]);
            
        }
	}
    
    public function actionCreate(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            try {
                $post = new Post();
                $post->text_comment = $_POST['text_comment'];
                $post->user_id = $this->user_id;
                $post->validate();
                $post->save();
                $created_post = Post::getOne();
                header("Location: /index.php#rec".$created_post->id);
            } catch(Exception $exc){
                $this->render("views/error.php", ['error' => $exc->getMessage()]);
            }
        } else {
            $this->render("views/error.php", ['error' => 'Метод добавления работает через POST.']);
        }
    }
    
    public function actionAnswer(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            try {
                $comm = new Comment();
                $comm->text_comment = $_POST['text_comment'];
                $comm->user_id = $this->user_id;
                $comm->parent_id = intval($_POST['parent_id']);
                $comm->save();
                $created_comm = Comment::getOne($comm->parent_id, -1);
                header("Location: /index.php#rec".$created_comm->id);
            } catch(Exception $exc){
                $this->render("views/error.php", ['error' => $exc->getMessage()]);
            }
        } else {
            $this->render("views/error.php", ['error' => 'Метод добавления работает через POST.']);
        }
    }
    
    public function actionDelete(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            try {
                $id = intval($_POST['id']);
                $obj = Post::getOne($id);
                if (!$obj){
                    $obj = Comment::getOne(0,$id);
                }
                if ($obj->user_id != $this->user_id){
                    throw new Exception('Запрещено удалять чужие комментарии');
                }
                $obj->delete();
                header("Location: /index.php");
            } catch(Exception $exc){
                $this->render("views/error.php", ['error' => $exc->getMessage()]);
            }
        } else {
            $this->render("views/error.php", ['error' => 'Метод удаления работает через POST.']);
        }
    }

    public function actionEdit(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            try {
                $id = intval($_POST['id']);
                $obj = Post::getOne($id);
                if (!$obj){
                    $obj = Comment::getOne(0,$id);
                }
                if ($obj->user_id != $this->user_id){
                    throw new Exception('Запрещено редактировать чужие комментарии');
                }
                $obj->text_comment = $_POST['text_comment'];
                $obj->save();
                header("Location: /index.php");
            } catch(Exception $exc){
                $this->render("views/error.php", ['error' => $exc->getMessage()]);
            }
        } else {
            $this->render("views/error.php", ['error' => 'Метод редактирования работает через POST.']);
        }
    }
	
	public function actionLogout(){
		setcookie( "facebook_id" , '' , time() - 3600);
		setcookie( "user_id" , '' , time() - 3600);
		header("Location: /index.php");
	}
    
    private function setAuthParams(){
        if (isset($_COOKIE["facebook_key"]) && $_COOKIE["user_id"] > 0){
            $this->user_id = $_COOKIE["user_id"];
        }
        $conf = new Config();
        $json_a = $conf->readConfigFile();
        $client_id = $json_a['client_id'];
        $this->client_secret = $json_a['client_secret'];
        $redirect_uri = 'http://sinepolsky.comxa.com/index.php'; // Redirect URIs
        $url = 'https://www.facebook.com/dialog/oauth';
        $params = array(
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'email,user_birthday'
        );
        $this->auth_params = $params;
        $this->auth_url = $url;
    }
    
    private function regFacebook(){
        $this->setAuthParams();
        if (isset($_GET['code']) && strlen($_GET['code'])>32){
            $params = array(
                    'client_id'     => $this->auth_params['client_id'],
                    'redirect_uri'  =>  $this->auth_params['redirect_uri'],
                    'client_secret' => $this->client_secret,
                    'code'          => $_GET['code']
            );
            $url = 'https://graph.facebook.com/oauth/access_token';
            $tokenInfo = null;
            parse_str(file_get_contents($url . '?' . http_build_query($params)), $tokenInfo);
            $access_tok_json = '';
            foreach ($tokenInfo as $key => $val){
                    $access_tok_json = $key;
                    break;
            }
            $tokenInfo = json_decode($access_tok_json,true);
            if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {
                $params = array('access_token' => $tokenInfo['access_token']);
                $userInfo = json_decode(file_get_contents('https://graph.facebook.com/me' 
                        . '?' . urldecode(http_build_query($params))), true);
                $u = User::getOne(0, $userInfo['id']);
                if (!$u){
                    $u = new User();
                    $u->facebook_id = $userInfo['id'];
                    $u->facebook_name = $userInfo['name'];
                    $u->save();
                }
                $u = User::getOne(0, $userInfo['id']);
                setcookie("facebook_key",$tokenInfo['access_token']);
                setcookie("user_id" , $u->id);
                $this->user_id = $u->id;
                return true;
            }
        }
        return false;
    }
    
    function __construct() {
        if ($this->regFacebook()){
            header("Location: /index.php");
            exit();
        }
        if (isset($_GET['action']) || isset($_POST['action'])){
            $act = strtolower((isset($_GET['action']) ? 
                    $_GET['action'] : $_POST['action'] ) );
            switch ($act){
                case 'index':
                    $this->actionIndex();
                    break;
                case 'messages':
                    $this->actionMessages();
                    break;
                case 'create':
                    $this->actionCreate();
                    break;
                case 'delete':
                    $this->actionDelete();
                    break;
                case 'edit':
                    $this->actionEdit();
                    break;
                case 'answer':
                    $this->actionAnswer();
                    break;
                case 'logout':
                    $this->actionLogout();
                    break;
                default :
                    $this->actionIndex();
            }
        } else {
            $this->actionIndex();
        }
    }
    
    
}
