<?php
/**
* @author nuxse
* @date 2016-04-15 11:11:07
* @description 应用服务器
*/

namespace lib;
class AppServer extends HttpServer{

	public $config;
	public static $ext = ['ico','html','jpg','png','webp','gif','rar','jpeg'];
	public static $auto_load = ['mongodb'];
	public function __construct($config){
		$this->config = $config;
		parent::__construct($config);
		$this->setRootNS();
	}

	public function __get($name){
        if(empty($this->$name) && in_array($name,self::$auto_load)){
            $class = '\lib\db\\'.ucwords($name);
            $this->$name = new $class(isset($this->config[$name]) ? $this->config[$name]: []);
        }else{
            $this->$name = null;
        }
        return $this->$name;
    }

	public function start(){
		ob_start();
		$ext = getExt();
		$action = ucwords(getAct());
		$method = getMethod();
		$act_class = '\\Controller\\'.$action.'Controller';
        if(!$ext){
        	$act_obj = new $act_class($this,$method);
	        //print_r($act_obj);exit();
	        $data = $act_obj->$method();
	        //print_r($data);
        }
		$respData = ob_get_clean();
		$respData = $respData?$respData:$ext;
		$this->response($respData);
	}

	public function setRootNS(){
		// Loader::setRootNS('App',DOCUMENT_ROOT);
         Loader::setRootNS('Controller',APP_PATH.'Controller/');
        // Loader::setRootNS('Widget',WIDGET_PATH);
        // Loader::setRootNS('Task',DOCUMENT_ROOT.'/Task/');
        // Loader::setRootNS('Cron',CRON_PATH);
        // Loader::setRootNS('AppMod',APP_PATH.'Mod/');
        // Loader::setRootNS('Plugin',APP_PATH.'Plugin/');
         Loader::setRootNS('Task',FRAME_PATH.'task/');
	}

}
