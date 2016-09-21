<?php
namespace lib\db;

class Mongodb implements lib\DB
{
	private $config;	//配置
	private $link; 		//当前链接
	private $db;		//当前数据集合
	private $error;		//错误信息

	//mongodb  构造函数 初始化链接 
	//在此阶段未指定操作的集合
	public function __construct($config)
	{
		if(!isset($config['host']) || !isset($config['db']))
		{
			exit('Mongodb not configured!');
		}
		$this->config = $config;
		!class_exists('MongoClient') && exit('mongodb depend on mongo-php-driver extension ');
		
		try{
			//依赖老版本的mongo-php-driver 
			//新的mongodb-php-driver 需要重写
			$this->link = new MongoClient($config['host']);
			$this->db = $this->link->$config['db'];
		}catch(\Exception $e){
			$this->error = $e->getMessage();
		}
	}

	//指定操作的集合 默认选中集合debug
	public function setCollection($collectionName = 'debug')
	{
		$this->collectionName = $collectionName;
	}

	//魔术方法  可用于调用mongodb的方法
	public function __call($name,$args){
		try{
			return call_user_func_array([$this->collection,$name], $args);
		}catch(\Exception $e){
			$this->error = $e->getMessage();
			return false;
		}
	}


	// /**
	// * 插入记录
	// * @access public
	// * @param array $array 要插入的数据，如果数据已存在，则插入失败
	// * @param array $options fsync:是否异步插入，异步即不等待mongodb操作完成，直接返回数据标识_id
	// *              			j:默认false,w,wtimeout,safe,timeout 
	// * @return bool
	// */
	// public function insert($array,$options=[]){
	// 	if(empty($array) || !is_array($array)){
	// 		throw new \Exception("The argument[0] must to a associative array", 909);
	// 	}
	// 	$this->formatData($array);
	// 	try{
	// 		return $this->collection->insert($array,$options);
	// 	}catch(\MongoCursorException $e){
	// 		$this->error = $e->getMessage();
	// 		return false;
	// 	}finally{
	// 		$this->reset();
	// 	}
	// }
	
	public function select()
	{
		
	}
	
}
