<?php
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
	$path_info = trim(val($_SERVER,'PATH_INFO'),'/');
	if(empty($path_info)){
		return 'index';
	}else{
		$path_info = explode('/',$path_info);
		return $path_info[0];
	}
}

//获取当前请求的方法名称
function getMethod(){

	$path_info = trim(val($_SERVER,'PATH_INFO'),'/');
	if(empty($path_info)){
		return 'index';
	}else{
		$path_info = explode('/',$path_info);
		return val($path_info,1,'index');
	}
}

//获取当期请求的后缀
function  getExt(){
	$path_info = trim(val($_SERVER,'PATH_INFO'),'/');
	$path_info = explode('.',$path_info);
	return val($path_info,1);
}

//获取 配置项
function config($config,$key)
{
	$key = explode('.',$key);
    $val = isset($config[$key[0]]) ? $config[$key[0]] : '';
    if(isset($key[1])){
        isset($val[$key[1]]) && ($val = $val[$key[1]]) || ($val = '');
    }
    return $val;
}

//mongodb
function  mongodb($collection = 'debug')
{
	static $mongodbs = [];
	global $appServer;

	$key = md5($collection);
	if(!isset($mongodbs[$key])){
		$mongodbs[$key] = clone $appServer->mongodb;
		$mongodbs[$key]->setCollection($collection);
	}
	return $mongodbs[$key];
}




/**
* 获取$_GET参数
* @param String $key
* @param Mix $default
* @param String $funs
* @return Mix
*/
function get($key,$default='',$funs=''){
  $val = _get_post($key,$funs,'get');
  return $val!==false ? $val : $default;
}

/**
* 获取$_POST参数
* @param String $key
* @param Mix $default
* @param String $funs
* @return Mix
*/
function post($key,$default='',$funs=''){
  $val = _get_post($key,$funs,'post');
  return $val!==false ? $val : $default;
}

/**
* 获取$_REQUEST参数
* @param String $key
* @param Mix $default
* @param String $funs
* @return Mix
*/
function request($key,$default='',$funs=''){
  $val = _get_post($key,$funs,'request');
  return $val!==false ? $val : $default;
}

/**
* get,post,request的处理函数
* @param String $key
* @param String $funs
* @param String $method
* @return Mix
*/
function _get_post($key,$funs='',$method='get'){
  if(empty($key)){
    throw new \Exception("key can not empty.eg:key[.key1[.key2......]]",120);
  }
  $arr = explode('.',$key);
  $method = strtolower($method);
  $data = $method == 'get' ? $_GET : ($method == 'post' ? $_POST : ($method == 'request' ? $_REQUEST : []));
  if(empty($data)){
    return false;
  }
  $key = array_shift($arr);
  $val = isset($data[$key]) ? $data[$key] : false;
  while($key = array_shift($arr)){
    $val = isset($val[$key]) ? $val[$key] : false;
  }
  if($val===false){
    return false;
  };
  if($funs){
    if(!is_array($funs)){
      $funs = explode(',',$funs);
      foreach($funs as $fun){
        /**
         * 不能使用没有返回主体的函数 eg:is_empty()
         * $param = 'aaa',需要调用函数转换为大写，需要返回AAA,
         * 而不能返回ture / fasle 
         */
        $val = call_user_func($fun,$val);
      }
    }else{
      $val = call_user_func($funs,$val);
    }
  }
  return $val;
}


/**
* 获取当前浏览器访问的链接
* @return String
*/
function cur_url(){
  if(isset($_SERVER['HTTP_HOST'])){
    if(isset($_SERVER['SERVER_PROTOCOL'])){
      $sch = strpos($_SERVER['SERVER_PROTOCOL'],'https') !==false ? 'https://' : 'http://';
    }else{
      $sch = 'http://';
    }
    $query_str = val($_SERVER,'QUERY_STRING');
    $query_str && $query_str = '?'.$query_str;
    //$url = $sch.val($_SERVER,'HTTP_HOST','unknown host').val($_SERVER,'REQUEST_URI').$query_str;
    $url ='http://iphp.nuxse.com/mongo'.val($_SERVER,'REQUEST_URI').$query_str;
    return $url;
  }
  return '';
}


/**
* 将输入转换为绝对整数
* @param numeric $num
* @return Int
*/
function absint($num){
  return abs(intval($num));
}