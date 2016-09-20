<?php
/**
* @author nuxse
* @date 2016-04-13 15:17:29
* @todo 加载器初始化
*/
use lib\Error;
//引入加载器
require_once(LIB_PATH.'Loader.php');

//初始化 加载目录
\lib\Loader::setRootNS('lib',LIB_PATH);
\lib\Loader::setRootNS('mod',MOD_PATH);

// 注册自动加载函数  创建一个新的对象 找不到则会调用自动加载函数
// 默认参数为当前需要创建的类名
spl_autoload_register('\\lib\Loader::autoload');

//自定义错误类
set_error_handler(array(new Error(),'error'), E_ALL);
set_exception_handler(array(new Error(),'exception'));
register_shutdown_function(array(new Error(),'shutdown'));
