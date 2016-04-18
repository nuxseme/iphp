<?php
##服务器入口
##环境检测
##定义环境变量
##引入内部函数库  内部函数必须以.php结尾
##开启服务
## swoole以php扩展形式内置 可直接使用

//环境检测

define('ENV','dev');

//取得应用
if(ENV==='pro' && isset($argv[1]) && empty($app_name = $argv[1])) {
    exit("Usage: appServer.php app_name" . PHP_EOL);
}
//PHP版本
if(version_compare(PHP_VERSION, '5.4.0','<')){
	exit('php version >=5.4.0 require'.PHP_EOL);
}

//是否安装了swoole扩展
if(!get_extension_funcs('swoole')){
	exit('HttpServer need swoole extension'.PHP_EOL);
}

//swoole版本检查
if (version_compare(SWOOLE_VERSION, '1.7.16', '<')) {
    exit("HttpServer Swoole >= 1.7.16 required " . PHP_EOL);
}


//需要exec执行命令
if (!function_exists('exec')) {
    exit("HttpServer must enable exec " . PHP_EOL);
}




//定义环境变量
define('FRAME_PATH', __DIR__.'/');
define('FUNCTION_PATH', __DIR__.'/function/');
define('LIB_PATH',__DIR__.'/lib/');
define('INIT_PATH', __DIR__.'/init/');
define('MOD_PATH',__DIR__.'/mod/');
define('CONFIG_PATH', __DIR__.'/config/');

//引入内部函数库
$function_files = getFiles(FUNCTION_PATH);
if(!empty($function_files) && is_array($function_files)){
	array_walk($function_files, function($file){
		$temp = explode('.', $file);
		if(isset($temp[1]) && $temp[1]==='php'){
			//内部函数必须以.php结尾
			if(is_file($file))
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

	$path = rtrim($path,'/');
    if(!is_dir($path))return [];
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

/**
 * [init 初始化]
 * @return [type] [description]
 */
function init(){
	$init_files = getFiles(INIT_PATH);
	if(!empty($init_files) && is_array($init_files)){
		array_walk($init_files, function($file){
			$temp = explode('.', $file);
			if(isset($temp[1]) && $temp[1]==='php'){
				if(is_file($file))
					require_once($file);
			}
		});
	}
}

//加载模块化的初始行为
init();
//加载配置
$config_file=CONFIG_PATH.'server.ini';
if(is_file($config_file))
	$config=parse_ini_file($config_file,true);

//全局挂载树
global $php;
$server = new Lib\AppServer($config);
//将应用服务器挂载到全局树上
$php = &$server;
//HttpServer  解析完 http请求体  appServer 接收
$server->setProcReqFun([$server,'start']);
$server->run();

