<?php
/**
* @author coco
* @date 2016-04-14 11:26:34
* @todo 框架服务器
*/

namespace Lib;
class HttpServer{
    public $config;
	public $_onRequest;//HttpServer转交给AppServer的回调函数
	public $http_server;//保存实例化的swoole
	public $rs;//响应句柄
    public $rq;//请求句柄
	public function __construct($config){
        $this->config = $config;
    }


	public function run(){
		$swcfg = array_merge($this->config,[
                'log_file' => FRAME_PATH.'log/httpServer.log',
                'max_request' => 100000,
                'max_conn' => 256,
                'daemonize' => 0,//是否退化为守护进程
            ]);
        $server = new \swoole_http_server(val($this->config,'host'), val($this->config,'port'));
        $this->http_server = $server;
        $server->set($swcfg); //设定swoole扩展的配置
         $server->on('Start',array($this,'onStart')); // 主进程开始 master 监听请求 转发至管理进程
         $server->on('ManagerStart', array($this,'onManagerStart')); //管理进程开始 manager 维护工作进程 安排工作,投递任务
         $server->on('ManagerStop', array($this,'onManagerStop')); 
         $server->on('WorkerStart',array($this,'onWorkerStart'));//工作任务进程 work||task 开始
         $server->on('Request', array($this, 'onRequest')); // 处理请求
        // $server->on('Close', array($this, 'onClose'));
        // $server->on('Shutdown', array($this, 'onShutdown'));
         $server->on('Task', array($this, 'onTask'));//任务调度
         $server->on('Finish', array($this, 'onFinish'));//任务结束
        // $server->on('WorkerStop',[$this,'onWorkerStop']);
        // $server->on('WorkerError',[$this,'onWorkerError']);
        // $server->on('Timeout',[$this,'onTimeout']);
        $server->start();
	}
    /**
    * 更改进程名称
    * @access public
    * @param string $name
    * @return void
    */
    public function setProcessName($name){
        swoole_set_process_name($name);
    }

    /**
    * 管理进程结束时回调函数
    * @access public
    * @param \swoole_server $serv
    * @return void
    */
    public function onManagerStop($serv){
        //$this->log(SERVER_NAME."[#manager] stop");
         exec("echo `date +'%m-%d %H:%M%:%S'` onManagerStop >>".FRAME_PATH."log/process.log");
    }

    /**
    * 管理进程启动时回调函数 设定管理work||task进程的进程的名称
    * @access public
    * @param \swoole_server $serv
    * @return void
    */
    public function onManagerStart($serv){
        global $config_file;
        $this->setProcessName('iphp-manager:('.$config_file.')');
       exec("echo `date +'%m-%d %H:%M%:%S'` ManagerStart >> ".FRAME_PATH."log/process.log");
        //$this->log(SERVER_NAME."[#manager] start");
    }
    
     /**
    * work进程启动时回调函数 设定work||task进程的名称
    * @access public
    * @param \swoole_server $server
    * @param int $worker_id
    * @return void
    */
    public function onWorkerStart($server,$worker_id){
        //$this->loadPlugin();
        exec("echo `date +'%m-%d %H:%M%:%S'` WorkerStart >> ".FRAME_PATH."log/process.log");
        if ($worker_id >= val($this->config,'worker_num')){
            $this->setProcessName('iphp-task:#'.$worker_id);
            //$this->log("php-task[#{$worker_id}] running on ".$this->c('server.host').":".$this->c('server.port'));
        }else{
            $this->setProcessName('iphp-worker:#'.$worker_id);
            //$this->log("php-worker[#{$worker_id}] running on ".$this->c('server.host').":".$this->c('server.port'));
            //apply_action('on_worker_start',$server,$worker_id);
        }
    }


    /**
    * 服务器关闭时回调函数
    * @access public
    * @param \swoole_server $serv
    * @return void
    */
    public function onShutdown($serv){
        //exec('rm -rf '.SHM_PATH);
        //$this->log(SERVER_NAME." shutdown");
        //apply_action('on_shutdown',$this,$serv);
    }

    /**
    * 服务器启动时回调函数 在主进程开始之前设置主进程的名称
    * @access public
    * @param \swoole_server $serv
    * @return void
    */
    public function onStart($serv){
        exec("echo `date +'%m-%d %H:%M%:%S'` start >> ".FRAME_PATH."log/process.log");
        $this->setProcessName('iphp-master:host=' . $this->config['host'] . ' port=' . $this->config['port']);
        //apply_action('server_start',$serv);
    }

	 /**
    * 请求处理函数
    * @access public
    * @param swoole_request $rq
    * @param swoole_response $rs
    * @return void
    */
    public function onRequest(\swoole_http_request $rq,\swoole_http_response $rs){

    	try{
    		$this->rs = $rs;
            $this->rq = $rq;
            $_SERVER = [];
            $_SERVER = $rq->server;
    		call_user_func($this->_onRequest);
    	}catch(Exception $e){}finally{}
    }




	/**
    * 处理请求回调函数
    * @access public
    * @param function $callback
    * @return void
    */
    public function setProcReqFun($callback){
        $this->_onRequest = $callback;
    }

    /**
    * 发送内容
    * @access public
    * @param \swoole_server $serv
    * @param int $fd
    * @param string $respData
    * @param int $code
    * @return void
    */
    public function response($respData, $code = 200){

    	$this->rs->write($respData);
		$this->rs->end();
        /*if(empty($this->rs)){
            return false;
        }
        $connection_info = $this->serv->connection_info($this->fd);
        if($connection_info==false){
            return false;
        }
        try{
            $this->c('gzip_level') && $this->rs->gzip($this->c('gzip_level'));
            $this->rs->status($code);
            $this->c('server.keepalive') && $this->setHeader('Connection','keep-alive');
            $strlen = strlen($respData);
            if($strlen > 1024*1024*2){
                $this->setHeader('Content-Length',$strlen);
                $p=0;
                $s=2000000;
                while($data = substr($respData, $p++*$s,$s)){
                    $this->rs->write($data);
                }
                $this->rs->end();
            }else{
                $this->rs->end((string)$respData);
            }
            return true;
        }catch(\Exception $e){
            $this->rs->status(500);
            $rs->end($e->getMessage());
            return true;
        }finally{
            $this->rs = null;
        }*/
        return true;
    }

    /**
    * 异步任务回调函数
    * @access public
    * @param \swoole_server $serv
    * @param int $task_id
    * @param int $from_id
    * @param string $data
    * @return void
    */
    public function onTask($serv, $task_id, $from_id, $data){
        try{
            $task_data = @json_decode(gzuncompress($data),true);
            if(empty($task_data)){
                return;
            }
            $task_name = $task_data['name'];
            $data = $task_data['data'];
            $task = '\\Task\\'.ucwords($task_name);
            $task::run($data);
        }catch(Exception $e){
            //$this->log($e->getMessage());
            print_r($e);
        }
         $serv->finish('finish');
    }

    /**
    * 异步任务结束时回调函数
    * @access public
    * @param \swoole_server $var
    * @param int $task_id
    * @param string $data
    * @return void
    */
    public function onFinish($serv, $task_id, $data){
        exec("echo task endendend  >> ".FRAME_PATH."log/task.log");
    }

}