<?php
/**
* @author coco
* @date 2016-04-18 16:55:55
* @todo 
*/

namespace Task;
class Task{
	public static function run($data){
		exec('echo hello task >> /home/www/iphp/log/task.log');
	}
}