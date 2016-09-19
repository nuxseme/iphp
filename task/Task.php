<?php
/**
* @author nuxse
* @date 2016-04-18 16:55:55
* @todo 
*/

namespace task;
class Task{
	public static function run($data){
		exec("echo `date +'%m-%d %H:%M:%S'` hello task >> ".FRAME_PATH."/log/task.log");
	}
}