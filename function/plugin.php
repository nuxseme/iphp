<?php
/**
* @author coco
* @date 2016-04-18 14:13:42
* @todo 
*/

/**
* 添加一个动作*
* @param String $type
* @param Mix $handler
* @param Int $weight
* @return Bool
*/
function add_action($type,$handler,$weight=1){
  global $php;
  $php->plugins[$type][$weight][] = $handler;
  return true;
}

/**
* 执行一批动作
* @param String $type
* @param mix $arg<n>，默认可接受最多8个参数，一般应用应该够了。
* @return void
*/
function apply_action(){
  global $php;
  $params = func_get_args();
  $type = array_shift($params);
  if(isset($php->plugins[$type]) && !empty($php->plugins[$type])){
    $actions = $php->plugins[$type];
    ksort($actions);
    foreach($actions as $action){
      foreach($action as $the_action){
        call_user_func_array($the_action, $params);
      }
    }
  }
}