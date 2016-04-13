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
		return isset($array[$key])?$array[$key]:$default;
	}elseif(is_object($obj)){
		return isset($obj->$key)?$obj->$key:$default;
	}
	return $default;
}