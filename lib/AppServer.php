<?php
/**
* @author coco
* @date 2016-04-15 11:11:07
* @todo 
*/

namespace Lib;
class AppServer extends HttpServer{

	public $config;
	
	public function __construct($config){
		$this->config = $config;
		parent::__construct($config);
        set_error_handler(array(new Error(),'error'), E_ALL);
        set_exception_handler(array(new Error(),'exception'));
        register_shutdown_function(array(new Error(),'shutdown'));
		$this->setRootNS();
	}


	public function start(){
		//ob_start();
		echo __METHOD__.PHP_EOL;
		echo 'ob_get_clean()';
		echo 'add_task';
		add_task('task',['data'=>123]);
		//$respData = ob_get_clean();
		$respData = 'helloworld';
		$this->response($respData);
	}

	public function setRootNS(){
		/*Loader::setRootNS('App',DOCUMENT_ROOT);
        Loader::setRootNS('Act',APP_PATH.'Act/');
        Loader::setRootNS('Widget',WIDGET_PATH);
        Loader::setRootNS('Task',DOCUMENT_ROOT.'/Task/');
        Loader::setRootNS('Cron',CRON_PATH);
        Loader::setRootNS('AppMod',APP_PATH.'Mod/');
        Loader::setRootNS('Plugin',APP_PATH.'Plugin/');*/
        Loader::setRootNS('Task',FRAME_PATH.'/task/');
	}
	

}
