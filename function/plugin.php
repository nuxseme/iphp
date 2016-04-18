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
function apply_action($type,&$arg1=null,&$arg2=null,&$arg3=null,&$arg4=null,&$arg5=null,&$arg6=null,&$arg7=null,&$arg8=null){
  global $php;
  if(isset($php->plugins[$type]) && !empty($php->plugins[$type])){
    $actions = $php->plugins[$type];
    ksort($actions);
    foreach($actions as $action){
      foreach($action as $the_action){
        call_user_func_array($the_action, [&$arg1,&$arg2,&$arg3,&$arg4,&$arg5,&$arg6,&$arg7,&$arg8]);
      }
    }
  }
}