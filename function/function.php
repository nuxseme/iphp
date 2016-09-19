<?php
/**
 * [helloworld 测试函数]
 * @return [type] [description]
 */
function helloworld(){
	echo 'helloworld';
}
/**
 * [val 安全返回键值函数]
 * @param  [type] $obj     [对象]
 * @param  [type] $key     [键值]
 * @param  [type] $default [默认值]
 * @return [type]          [返回值]
 */

function val(&$obj,$key,$default=''){
	if(is_array($obj)){
		return isset($obj[$key])?$obj[$key]:$default;
	}elseif(is_object($obj)){
		return isset($obj->$key)?$obj->$key:$default;
	}
	return $default;
}

//获取当前请求的控制器名称
function getAct(){
	$path_info = trim(val($_SERVER,'path_info'),'/');
	if(empty($path_info)){
		return 'index';
	}else{
		$path_info = explode('/',$path_info);
		return $path_info[0];
	}
}

//获取当前请求的方法名称
function getMethod(){

	$path_info = trim(val($_SERVER,'path_info'),'/');
	if(empty($path_info)){
		return 'index';
	}else{
		$path_info = explode('/',$path_info);
		return val($path_info,1,'index');
	}
}

//获取当期请求的后缀
function  getExt(){
	$path_info = trim(val($_SERVER,'path_info'),'/');
	$path_info = explode('.',$path_info);
	return val($path_info,1);
}