<?php
##服务器入口
##环境检测
##定义环境变量
##引入内部函数库  内部函数必须以.php结尾
##开启服务


//定义环境变量
define('FUNCTION_PATH', __DIR__.'/function/');
define('LIB_PATH',__DIR__.'/lib/');
define('INIT_PATH', __DIR__.'/init/');


//引入内部函数库
$function_files = getFiles(FUNCTION_PATH);
if(!empty($function_files)){
	array_walk($function_files, function($file){
		$temp = explode('.', $file);
		if(isset($temp[1]) && $temp[1]==='php'){
			//内部函数必须以.php结尾
			require_once($file);
		}
	});
}

/**
 * [getFiles 获取指定目录]
 * @param  [type] $path [开始递归的目录]
 * @return [type]       [返回一维数组]
 */
function getFiles($path){
	if(substr($path,-1)==='/'){
		$path = rtrim($path,'/');
	}
    if(!is_dir($path))return $path.' 不是合法的路径';
    $files_pool = [];
	$files = scandir($path);
	foreach ($files as $item) {
		if($item != '.' && $item != '..' ){
			$item_abspath = $path.'/'.$item;
			if(is_file($item_abspath)){
				$files_pool[] = $item_abspath;
			}elseif(is_dir($item_abspath)){
				$files_pool= array_merge($files_pool,getFiles($item_abspath));
			}
		}
	}
	return $files_pool;
}

//加载模块化的初始行为
init();

//全局挂载树
global $php;

$server = new \Lib\AppServer();

function init(){
	$init_files = getFiles(INIT_PATH);
	if(!empty($init_files)){
	array_walk($init_files, function($file){
		$temp = explode('.', $file);
		if(isset($temp[1]) && $temp[1]==='php'){
			require_once($file);
		}
	});
}


}
