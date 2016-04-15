<?php
/**
* @author coco
* @date 2016-04-15 11:11:07
* @todo 
*/

namespace Lib;
class AppServer extends HttpServer{

	/**
	 * 设定错误 异常 shutdown 句柄
	 * 
	 */
	public function __construct(){

        set_error_handler(array(new Error(),'error'), E_ALL);
        set_exception_handler(array(new Error(),'exception'));
        register_shutdown_function(array(new Error(),'shutdown'));
		
	}


	public function start(){

		echo __METHOD__.PHP_EOL;

		$respData = ob_get_clean();
		$this->response($respData);
	}

	

}