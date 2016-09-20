<?php
/**
* @author nuxse
* @date 2016-04-14 16:59:38
* @todo 
*/
namespace lib;
class Error {

	/**
	 * [error 自定义错误处理]
	 * @param  [type] $error_level   [错误级别]
	 * @param  [type] $error_message [错误信息]
	 * @param  [type] $error_file    [错误文件]
	 * @param  [type] $error_line    [错误行号]
	 * @param  [type] $error_context [错误上下文]
	 * @return [type]                [description]
	 */
	public function  error($error_level,$error_message,$error_file,$error_line,$error_context)
	{
		$error_data = [

			'error_level'=> $error_level,
			'error_message'=> $error_message,
			'error_file'=> $error_file,
			'error_line'=> $error_line,
			'error_context' => $error_context
		];
		switch ($error_level) {  

			case E_WARNING:
				echo '[ERROR_LEVLE] : SYSTEM E_WARNING'.PHP_EOL;
				echo "[ERROR_MESSAGE] : $error_message".PHP_EOL;
				echo "[ERROR_LINE] : $error_line".PHP_EOL;
				echo "[ERROR_FILE] : $error_file".PHP_EOL;
				break;

			case E_NOTICE:
				echo '[ERROR_LEVLE] : SYSTEM E_NOTICE'.PHP_EOL;
				echo "[ERROR_MESSAGE] : $error_message".PHP_EOL;
				echo "[ERROR_LINE] : $error_line".PHP_EOL;
				echo "[ERROR_FILE] : $error_file".PHP_EOL;
				break;
				
	    	case E_USER_ERROR:  
		     	echo '[ERROR_LEVLE] : SYSTEM E_USER_ERROR'.PHP_EOL;
				echo "[ERROR_MESSAGE] : $error_message".PHP_EOL;
				echo "[ERROR_LINE] : $error_line".PHP_EOL;
				echo "[ERROR_FILE] : $error_file".PHP_EOL;
				exit();
	  
	    	case E_USER_WARNING:  
	        	echo '[ERROR_LEVLE] : SYSTEM E_USER_WARNING'.PHP_EOL;
				echo "[ERROR_MESSAGE] : $error_message".PHP_EOL;
				echo "[ERROR_LINE] : $error_line".PHP_EOL;
				echo "[ERROR_FILE] : $error_file".PHP_EOL;
				break;
	  
	    	case E_USER_NOTICE:  
	       		echo '[ERROR_LEVLE] : SYSTEM E_USER_NOTICE'.PHP_EOL;
				echo "[ERROR_MESSAGE] : $error_message".PHP_EOL;
				echo "[ERROR_LINE] : $error_line".PHP_EOL;
				echo "[ERROR_FILE] : $error_file".PHP_EOL;
				break;

			case E_ALL:  
	       		echo '[ERROR_LEVLE] : SYSTEM E_ALL'.PHP_EOL;
				echo "[ERROR_MESSAGE] : $error_message".PHP_EOL;
				echo "[ERROR_LINE] : $error_line".PHP_EOL;
				echo "[ERROR_FILE] : $error_file".PHP_EOL;
				break;
	    }  
	    /**
	     * 系统错误溯源函数
	     */
	    //debug_print_backtrace();
	    //print_r(deubug_backtrace());
	    return true;  
	}

	public function exception($e){
		echo __METHOD__.PHP_EOL;
		print_r($e);
	}

	public function shutdown(){
		echo __METHOD__.PHP_EOL;

	}

}
