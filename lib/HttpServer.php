<?php
/**
* @author nuxse
* @todo 框架服务器
*/

namespace lib;
class HttpServer{
    public $config;
	public $_onRequest;//HttpServer转交给AppServer的回调函数
	public $http_server;//保存实例化的swoole
	public $rs;//响应句柄
    public $rq;//请求句柄
    public $fd;//当前连接号
    //构造函数 保存应用配置
	public function __construct($config){
        $this->config = $config;
    }
    //swoole http服务器运行
	public function run(){
		$swoole_config = array_merge([
                'log_file' => FRAME_PATH.'log/httpServer.log',
                'max_request' => 100000,
                'max_conn' => 256,
                'daemonize' => 1,//是否退化为守护进程
            ],config($this->config,'global'));
        $server = new \swoole_http_server(config($this->config,'server.host'), config($this->config,'server.port'));
        $this->http_server = $server;
        $server->set($swoole_config); //设定swoole扩展的配置
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
    * 更改进程名称 不适用于osx
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
         exec("echo `date +'%m-%d %H:%M%:%S'` onManagerStop >>".FRAME_PATH.'log/'.APP_NAME.'process.log');
    }

    /**
    * 管理进程启动时回调函数 设定管理进程名称
    * @access public
    * @param \swoole_server $serv
    * @return void
    */
    public function onManagerStart($serv){
        global $config_file;
        $this->setProcessName(APP_NAME.':iphp-manager:('.$config_file.')');
       exec("echo `date +'%m-%d %H:%M%:%S'` ManagerStart >> ".FRAME_PATH.'log/'.APP_NAME.'process.log');
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
        if ($worker_id >= config($this->config,'global.work_num')){
            $this->setProcessName(APP_NAME.':iphp-task:#'.$worker_id);
            //$this->log("php-task[#{$worker_id}] running on ".$this->c('server.host').":".$this->c('server.port'));
        }else{
            $this->setProcessName(APP_NAME.':iphpworker:#'.$worker_id);
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
        $this->setProcessName(APP_NAME.':iphp-master: host=' . config($this->config,'server.host') . ' port=' . config($this->config,'server.port'));
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
        // $connection_info = $this->http_server->connection_info($rq->fd);
        // if($connection_info==false){
        //     return false;
        // }
    	try{
    		$this->rs = $rs;
            $this->rq = $rq;
            $this->fd = $rq->fd;
            $_SERVER = $rq->server;
            isset($rq->cookie) && $_COOKIE = $rq->cookie;
            isset($rq->files) && $_FILES = $rq->files;
            isset($rq->get) && $_GET = $rq->get;
            isset($rq->post) && $_POST = $rq->post;
            if($this->isMulFormData(val($rq,'header')) && $this->isArrayPost($_POST)){
                $_POST = $this->post2Array($_POST);
            }
            $_REQUEST = array_merge($_GET,$_POST);
            $GLOBALS['rawContent'] = '';
            //$connection_info && 
            $GLOBALS['rawContent'] = $rq->rawContent();
            $header = $server = [];
            if(isset($rq->header)){
                foreach($rq->header as $key=>$val){
                    $header['HTTP_'.strtoupper(str_replace('-','_',$key))] = $val;
                }
            }
            if(isset($rq->server)){
                $server = array_change_key_case($rq->server,CASE_UPPER);
            }
            $_SERVER = array_merge($header,$server);
            unset($server,$header);
            $_SERVER['REMOTE_ADDR'] = val($_SERVER,'HTTP_X_FORWARDED_FOR',$_SERVER['REMOTE_ADDR']);
            $_SERVER['SERVER_SOFTWARE'] = SERVER_NAME;
            if(defined('DEBUG')){
                $query_str = val($_SERVER,'QUERY_STRING');
                $query_str && $query_str = '?'.$query_str;
            }
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
        //没有响应句柄 
        if(empty($this->rs)){
            return false;
        }
        $connection_info = $this->http_server->connection_info($this->fd);
        if($connection_info==false){
            return false;
        }
        try{
            $gzip_level = config($this->config,'global.gzip_level') && $this->rs->gzip($gzip_level);
            $this->rs->status($code);
            config($this->config,'server.keepalive') && $this->setHeader('Connection','keep-alive');
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
        }
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


    /**
    * 设置发送内容头部
    * @access public
    * @param string $key
    * @param string $value
    * @return void
    */
    public function setHeader($key, $value){
        $this->rs->header($key,$value);
    }

      /**
    * 设置发送内容的cookie
    * @access public
    * @param string $name
    * @param string $value
    * @param int $expires
    * @param string $path
    * @param string $domain
    * @return void
    */
    public function setCookie($name,$value,$expires=0,$path='/',$domain='',$secure = false,$httponly = false){
        $this->rs->cookie($name,$value,$expires ? time()+$expires : 0,$path,$domain,$secure,$httponly);
    }

    /**
    * 检测是否表单multipart/form-data提交方式
    * @access private
    * @return bool
    */
    private function isMulFormData($header){
        if(explode(';',val($header,'content-type',''),2)[0]=='multipart/form-data'){
            return true;
        }
        return false;
    }

    /**
    * 当表单中有文件上传时，POST的数据不能解析name[key]的情况，需要检测，然后手工处理
    * @access private
    * @param array $post_data
    * @return bool
    */
    private function isArrayPost($post_data){
        foreach ($post_data as $key => $value) {
            if(strpos($key, '[')){
                return true;
            }
        }
        return false;
    }

    /**
    * 当表单中有文件上传时，POST的数据不能解析name[key]的情况，需手工处理
    * @access private
    * @param array $post_data
    * @return array
    */
    private function post2Array($post_data){
        $data = array2query($post_data);
        $ret = [];
        parse_str($data,$ret);
        return $ret;
    }

}