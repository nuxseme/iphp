<?php
/**
* @author coco
* @date 2016-04-13 15:18:49
* @todo 
*/
namespace Lib;
class Loader{
	
	static $nsPath; //命名空间路径

	//需要重新整理 S
	static $swoole;
	static $_objects;
	
	function __construct($swoole){
		self::$swoole = $swoole;
		self::$_objects = array(
				'model'=>[],
				'lib'=>[],
				'object'=>[]);
	}

	/**
	 * [loadModel 加载模型]
	 * @param  string $model_name [name]
	 * @param  string $prefix     [前缀]
	 * @return [obj]             [模型]
	 */
	static function loadModel($model_name='',$prefix=''){
		if(empty($model_name)){
			return new \Lib\Model;
		}
		$mod_key = $model_name;
		if(isset(self::$_objects['model'][$mod_key])){
			//除去模型已经存在的error
			self::$_objects['model'][$mod_key]->clearError();
			return self::$_objects['model'][$mod_key];
		}else{
			if($model_name{0}=='#'){
				$model_name = substr($model_name,1);
				$mod = '\\Mod\\'.$model_name;
			}elseif($model_name{0}=='@'){
				$mod = '\\AppMod\\'.$model_name;
			}else{
				$mod = '\\App\\Mod\\'.$model_name;
			}
			self::$_objects['model'][$mod_key] = new $mod($model_name,$prefix);
			return self::$_objects['model'][$mod_key];
		}
	}

	//需要重新整理 S

	
	/**
	 * [autoload 自动加载函数]
	 * @param  [type] $class [类名]
	 * @return [type]        [description]
	 */
	static function autoload($class){
		$root = '';
		foreach(self::$nsPath as $key=>$val){
			$key_arr = explode('\\',$key);
			$class_arr = explode('\\',trim($class,'\\'));
			if($key_arr[0]==$class_arr[0]){
				$root = $val;
				$class = substr($class,strlen($key)+1);
				break;
			}
		}
		$file_path = str_replace('\\','/', $root).str_replace('\\','/', $class).'.php';
		if(is_file($file_path)){
			require_once($file_path);
		}
	}
	/**
	 * [setRootNS 设置命名空间]
	 * @param [type] $root [根目录]
	 * @param [type] $path [对应的路径]
	 */
	static function setRootNS($root, $path){
		self::$nsPath[$root] = $path;
	}
}
