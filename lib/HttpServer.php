<?php
/**
* @author coco
* @date 2016-04-14 11:26:34
* @todo 
*/

namespace Lib;
class HttpServer{

	public $_onRequest;//HttpServer转交给AppServer的回调函数
	//public $serv;
	public $rs;//响应句柄
	public function __construct(){}


	public function run(){
		echo __METHOD__.PHP_EOL;

		$swcfg = [
                'log_file' => '/home/www/iphp/log/httpServer',
                'worker_num' => 8,
                'max_request' => 100000,
                'max_conn' => 10000,
                'daemonize' => 0,
            ];
       
        $server = new \swoole_http_server('0.0.0.0', 80);
        //$this->serv = $server;
        $server->set($swcfg);
        //$this->config = array_merge($this->config,$server->setting);
        // $server->on('Start',array($this,'onStart'));
        // $server->on('ManagerStart', array($this,'onManagerStart'));
        // $server->on('ManagerStop', array($this,'onManagerStop'));
        // $server->on('WorkerStart',array($this,'onWorkerStart'));
         $server->on('Request', array($this, 'onRequest'));
        // $server->on('Close', array($this, 'onClose'));
        // $server->on('Shutdown', array($this, 'onShutdown'));
        // $server->on('Task', array($this, 'onTask'));
        // $server->on('Finish', array($this, 'onFinish'));
        // $server->on('WorkerStop',[$this,'onWorkerStop']);
        // $server->on('WorkerError',[$this,'onWorkerError']);
        // $server->on('Timeout',[$this,'onTimeout']);
        $server->start();
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

    	$this->rs->write('hello client');
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



}