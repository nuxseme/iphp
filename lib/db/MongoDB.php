<?php
namespace lib\db;

class Mongodb implements db
{
	private $config;	//配置

	private $link; 		//当前链接句柄
	private $db;		//当前数据库句柄
	private $collection; //当前集合句柄

	private $dbName; //当前数据库名称
	private $collectionName;//当前集合名称
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
		$this->dbName = $config['db'];

		!class_exists('MongoClient') && exit('mongodb depend on mongo-php-driver extension ');
		
		try{
			//依赖老版本的mongo-php-driver 
			//新的mongodb-php-driver 需要重写
			$this->link = new \MongoClient($config['host']);
			$this->db = $this->link->$config['db'];
		}catch(\Exception $e){
			$this->error = $e->getMessage();
		}
	}

	//指定操作的集合 默认选中集合debug
	public function setCollection($collectionName = 'debug')
	{
		$this->collectionName = $collectionName;
		$this->collection = $this->db->$collectionName;
	}

	//魔术方法  可用于调用mongodb的方法
	public function __call($name,$args){
		try{
			$collection = $this->collectionName;
			return call_user_func_array([$this->db->$collection,$name], $args);
		}catch(\Exception $e){
			$this->error = $e->getMessage();
			return false;
		}
	}

	//魔术方法 返回未定义的属性获取方法
	public function __get($name)
	{
		$function = 'get'.ucwords($name);
		return $this->$function;
	}

	//返回当前集合连接句柄
	public function getCollection()
	{
		$collectionName = $this->collectionName;
		return $this->db->$collectionName;
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
	

	//仅仅调用了mongo 内置的insert 未设置options 
	//成功返回数组Array ( [ok] => 1 [n] => 0 [err] => [errmsg] => )
	//希望成功返回_id
	public function insert($data){

		$insert_result = $this->collection->insert($data);
	    return $insert_result;
	}

	
	//获取数据库下所有集合的对象
	//返回的结果是一个包含集合句柄的数组
	//Array ( [0] => MongoCollection Object ( [w] => 1 [wtimeout] => 10000 ) [1] => MongoCollection Object ( [w] => 1 [wtimeout] => 10000 ) )
	public function listCollections($db=null,$options=[]){
		if(!empty($db)){
			return $this->link->$db->listCollections($options);
		}else{
			return $this->db->listCollections($options);
		}
	}

	//获取指定数据库下的集合名称
	//Array ( [0] => collectionName1 [1] => collectionName2 )
	public function getCollectionNames($db=null)
	{
		if(!empty($db)){
			return $this->link->$db->getCollectionNames();
		}else{
			return $this->db->getCollectionNames();
		}
	}


	//列出数据库
	//返回数据格式
	//[
	//  databases => [
	//  				0 => [
	//  						'name' => database_name
	//  						'sizeOnDisk' => int
	//  						'empty' => 
	//  					 ]	
	//  					 
	//  			   
	//  			 ]
	//  totalSize] => int
	//  'ok' => 1
	//]
	public function listDBs()
	{
		//在当前的连接下显示数据库
		return  $this->link->listDBs();
	}

	//获取错误信息
	public function getError(){
		return $this->error;
	}



	public function multiInsert(){}
	public function delete(){}
	public function select(){}
	public function update(){}
	
}
