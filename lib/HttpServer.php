<?php
/**
* @author coco
* @date 2016-04-14 11:26:34
* @todo 
*/

namespace Lib;
class HttpServer{
    public $config;
	public $_onRequest;//HttpServer转交给AppServer的回调函数
	public $http_server;//保存实例化的swoole
	public $rs;//响应句柄
	public function __construct($config){
        $this->config = $config;
    }


	public function run(){
		echo __METHOD__.PHP_EOL;

		$swcfg = [
                'log_file' => FRAME_PATH.'/log/httpServer.log',
                'worker_num' => 1,
                'max_request' => 100000,
                'max_conn' => 256,
                'daemonize' => 1,//是否退化为守护进程
                'task_worker_num'=>1//工作进程数
            ];

        $server = new \swoole_http_server(val($this->config,'ip'), val($this->config,'port'));
        $this->http_server = $server;
        $server->set($swcfg);
        //$this->config = array_merge($this->config,$server->setting);
        $server->on('Start',array($this,'onStart'));
         $server->on('ManagerStart', array($this,'onManagerStart'));
         $server->on('ManagerStop', array($this,'onManagerStop'));
         //$server->on('WorkerStart',array($this,'onWorkerStart'));
         $server->on('Request', array($this, 'onRequest'));
        // $server->on('Close', array($this, 'onClose'));
        // $server->on('Shutdown', array($this, 'onShutdown'));
         $server->on('Task', array($this, 'onTask'));
         $server->on('Finish', array($this, 'onFinish'));
        // $server->on('WorkerStop',[$this,'onWorkerStop']);
        // $server->on('WorkerError',[$this,'onWorkerError']);
        // $server->on('Timeout',[$this,'onTimeout']);
        $server->start();
	}

    /**
    * 主进程启动时回调函数
    * @access public
    * @param \swoole_server $serv
    * @return void
    */
    public function onMasterStart($serv){
       // $this->log(SERVER_NAME."[#master] start");
       exec("echo `date +'%m-%d %H:%M%:%S'` masterstart >> /home/www/iphp/log/process.log");
    }

      /**
    * 管理进程启动时回调函数
    * @access public
    * @param \swoole_server $serv
    * @return void
    */
    public function onManagerStart($serv){
        global $config_file;
        $this->setProcessName('php-manager: ('.$config_file.')');
        //$this->log(SERVER_NAME."[#manager] start");
         exec("echo `date +'%m-%d %H:%M%:%S'` onManagerStart >> /home/www/iphp/log/process.log");
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
         exec("echo `date +'%m-%d %H:%M%:%S'` onManagerStop >> /home/www/iphp/log/process.log");
    }


	public function onStart(){

		echo __METHOD__.PHP_EOL;
	}

	 /**
    * 请求处理函数
    * @access public
    * @param swoole_request $rq
    * @param swoole_response $rs
    * @return void
    */
    public function onRequest(\swoole_http_request $rq,\swoole_http_response $rs){
    //public function onRequest(){

    	try{
    		echo __METHOD__.PHP_EOL;
    		$this->rs = $rs;
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
        // $serv->finish();
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
        //echo 'task data:'.$data;
        $path = FRAME_PATH;
        exec("echo task end  >> /home/www/iphp/log/task.log");
    }

}