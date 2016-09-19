<?php
/**
* @author hyd
* @date 09-18-2016 09:33
* @todo 创建应用配置文件
* [usage]  $php create_app_config.php  app_name port
*/
$fail="[\033[1;31mfail\033[0m]";
$ok="[\033[1;32mOK\033[0m]";
$src_config_file = './server.ini.temp';
$app_name = $argv[1];
$port = isset($argv[2]) ? $argv[2] : 9876;
$desc_config_file = './'.$app_name.'.ini';

if(is_file($desc_config_file))
{
	echo "{$app_name}.ini is exist".PHP_EOL;
	echo "create {$app_name}.ini {$fail}".PHP_EOL;
	return ;
}
$content = file_get_contents($src_config_file);
$content = str_replace(['{app_name}','{port}'],[$app_name,$port],$content);

//创建配置文件并替换
file_put_contents($desc_config_file,$content);
if(is_file($desc_config_file))
{
	echo "create a new app config file {$app_name}.ini {$ok}".PHP_EOL;
}else{
	echo "create a new app config file {$app_name}.ini {$fail}".PHP_EOL;
}

//创建应用目录和结构目录
$create_dir = ['Controller','Model','Task','Plugin','Widget','Cron','Lib','Tpl'];

$config_file = './'.$app_name.'.ini';
$config = parse_ini_file($config_file, true);
$app_path = $config['app']['app_path'].'/';

foreach($create_dir as $item){
  $path = $app_path.$item;
  if(!is_dir($path)){
    mkdir($path,0755,true);
    chown($path, 'www');
    chgrp($path, 'www');
    echo "create dir {$path}  {$ok}".PHP_EOL;
  }else{
  	echo "dir {$path} is exist".PHP_EOL;
  	echo "create dir {$path}  {$fail}".PHP_EOL;
  }
}

//创建默认控制器
$index_file = $app_path.'Controller/IndexController.php';
if(!is_file($index_file))
{
  $index_html = <<<eof
<?php
/**
* @author nuxse
* @date 
* @todo 默认控制器
*/
namespace Controller;
use \lib\Controller;
class IndexController  extends Controller
{

    public function index()
    {
      echo __METHOD__.PHP_EOL;
    }

}
eof;
file_put_contents($index_file,$index_html);
echo "create file IndexController {$ok}".PHP_EOL;
}