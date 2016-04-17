<?php
/**
* @author coco
* @date 2016-04-15 11:11:07
* @todo 
*/

namespace Lib;
class AppServer extends HttpServer{

	public $config;
	/**
	 * 设定错误 异常 shutdown 句柄
	 * 
	 */
	public function __construct($config){
		$this->config = $config;
		parent::__construct($config);
        set_error_handler(array(new Error(),'error'), E_ALL);
        set_exception_handler(array(new Error(),'exception'));
        register_shutdown_function(array(new Error(),'shutdown'));
		
	}


	public function start(){
		ob_start();
		print_r($this->config);
		echo __METHOD__.PHP_EOL;
		echo '测试ob_get_clean()';
		$respData = ob_get_clean();
		$this->response($respData);
	}

	

}