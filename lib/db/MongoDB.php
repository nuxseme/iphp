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
	private $options = [];	//操作
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
		return $this;
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

	
	/**
	 * [insert description]
	 * @param  [type] $data    [插入的文档]
	 * @param  array  $options [fsync:是否异步插入，异步即不等待mongodb操作完成，直接返回数据标识_id
	 *              			j:默认false,w,wtimeout,safe,timeout ]
	 * @return [type]          [_id 对象]
	 */
	public function insert($data,$options = []){
		try{
		 	$this->collection->insert($data, $options);
		 	return $data['_id'];
		}catch(\MongoCursorException $e){
			$this->error = $e->getMessage();
			return false;
		}finally{
			$this->reset();
		}
	}

	//删除文档  
	//justOne : （可选）如果设为 true 或 1，则只删除一个文档。
	//writeConcern :（可选）抛出异常的级别。
	public function delete($where,$options)
	{
		try{
			return $this->collection->remove($where,$options);
		}catch(\MongoCursorException $e){
			$this->error = $e->getMessage();
			return false;
		}
	}

	//返回查询数组 
	public function select($where = [])
	{
		$options = $this->getOptions();
		try{
			$collections = $this->collection->find($where ,$options['fields']);
			if(!empty($options['limit']))
			{
				$collections->limit($options['limit']);
			}			
			if(!empty($options['skip']))
			{
				$collections->skip($options['skip']);
			}			
			if(!empty($options['order']))
			{
				$collections->sort($options['order']);
			}
			foreach ($collections as $value) {
				$result[] = $value;
			}
			unset($collections);
			return $result;
		}catch (Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}

	//分页
	public function limit($skip = 0, $limit = 1)
	{
		$this->options['skip'] = $skip;
		$this->options['limit'] =  $limit;
		return $this;
	}

	//排序
	public function order($order){
		if(empty($order)){
			return $this;
		}
		if(!is_array($order)){
			$order = $this->order2array($order);
		}
		$this->options['order'] = $order;
		return $this;
	}

	//排序字段转换
	private function order2array($order){
		$sort = [];
		$orders = explode(',',$order);
		foreach ($orders as $key => $order) {
			list($s,$o) = preg_split('/\s+/',$order,2);
			$o = $o == 'asc' ? 1 : -1;
			$sort[] = [$s=>$o];
		}
		return $sort;
	}

	//选中字段
	public function fields($fields){
		if(empty($fields)){
			return $this;
		}
		if(!is_array($fields)){
			$fields = $this->fields2array($fields);
		}
		$this->options['fields'] = $fields;
		return $this;
	}

	//字段格式转化
	private function fields2array($fields){
		$fields = explode(',',$fields);
		$ret = [];
		foreach ($fields as $key => $field) {
			$ret[$field] = true;
		}
		return $ret;
	}

	//获取操作参数
	public function getOptions(){
		$default = [
			'fields' => [],
			'limit' => '',
			'skip' => 0,
			'order' => [],
		];

		return array_merge($default,$this->options);
	}

	//获取最先匹配的文档
	public function fetch($where){
		try{
			return $this->collection->findOne($where);
		}catch (Exception $e) {
			$this->error = $e->getMessage();
			return $e->getMessage();
		}
	}
	
	//Array ( [ok] => 1 [nModified] => 0 [n] => 0 [err] => [errmsg] => [updatedExisting] => )
	//修改单条
	//多条设定multi:true
	public function update($where,$data,$options)
	{
		try{
			return $this->collection->update($where,['$set'=>$data],$options);
		}catch (Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
		
	}

	//保存
	public function save($data)
	{
		try{
			return $this->collection->save($data);
		}catch (Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
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
	//切换数据库
	public function selectDB($db){
		$this->link->selectDB($db);
		$this->db = $this->link->$db;
		return $this;
	}

	//批量插入
	public function multiInsert($data, $options = [])
	{
		try{
			return $this->collection->batchInsert($data,$options);
		}catch(\MongoCursorException $e){
			$this->error = $e->getMessage();
			return false;
		}
	}

	//删除集合
	public function drop($collection_name=null){
		if(!empty($collection_name)){
			return $this->db->$collection_name->drop();
		}
		return $this->collection->drop();
	}

	//删除数据库
	public function dropDatabase(){
		return $this->db->dropDatabase();
	}

	 //执行mongodb命令
	public function runCommand($command_data,$options=[]){
		if(empty($command_data)){
			return false;
		}
		return $this->db->command($command_data,$options=[]);
	}
	//复位
	private function reset(){
		$this->options = [];
	}
	
}
