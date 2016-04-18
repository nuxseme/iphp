<?php
/**
* @author coco
* @date 2016-04-18 14:21:37
* @todo 
*/
/**
* 添加一个异步任务
* @param string $task_name 任务名称
* @param arrau $task_data 任务数据
* @return bool
*/
function add_task($task_name,$task_data){
  global $php;
  // 在用户自定义的进程里，是无法调用异步任务
  $data = gzcompress(json_encode(['name'=>$task_name,'data'=>$task_data]),5);
  return $php->http_server->task($data);
}
