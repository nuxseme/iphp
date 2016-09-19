<?php
/**
* @author coco
* @date 2016-04-14 16:59:38
* @todo 
*/
namespace lib;
class Error{

	
	public function  error($errno, $errstr, $errfile, $errline){
		$err_data = [

			'errno'=> $errno,
			'errstr'=> $errstr,
			'errfile'=> $errfile,
			'errline'=> $errline,
		];

		switch ($errno) {  
	    case E_USER_ERROR:  
	  
	     echo "<b>My ERROR</b> [$errno] $errstr<br />\n";  
	        echo "  Fatal error on line $errline in file $errfile";  
	        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";  
	        echo "Aborting...<br />\n";  
	        exit(1);  
	        break;  
	  
	    case E_USER_WARNING:  
	        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";  
	        break;  
	  
	    case E_USER_NOTICE:  
	        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";  
	        break;  
	  
	    default:  
	        echo "Unknown error type: [$errno] $errstr<br />\n";  
	        break;  
	    }  
	  
	    /* Don't execute PHP internal error handler */  
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
