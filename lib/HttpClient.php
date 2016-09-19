<?php
/**
* @author coco
* @date 2016-03-09 17:33:27
* @todo 
*/
namespace lib;
class HttpClient{
    const PORT = 80;
    protected $timeout = 30;
    public $url;
    public $uri;
    public $reqHeader;
   
    protected $cli;
    protected $buffer = '';
    protected $nparse = 0;
    protected $isError = false;
    protected $isFinish = false;
    protected $status = array();
    protected $respHeader = array();
    protected $body = '';
    protected $trunk_length = 0;
    protected $userAgent = 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';
    protected $onReadyCallback;
    protected $post_data;
    protected $method = 'GET';
    /**
     * [__construct 构造函数初始化url/port]
     * @param [type] $url [description]
     */
    public function __construct($url){
        $this->url = $url;
        $this->uri = parse_url($this->url);
        if (empty($this->uri['port'])){
            $this->uri['port'] = self::PORT;
        }
    }

    /**
     * [execute 请求主控函数]
     * @return [type] [description]
     */
    public function execute(){
        /*if (empty($this->onReadyCallback)){
            //throw new \Exception(__CLASS__." require onReadyCallback");
        }*/
        $cli = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);//TCP模式 异步非阻塞 
        $this->cli = $cli;
        $cli->on('connect', array($this, 'onTest'));
        $cli->on('error', array($this, 'onError'));
        $cli->on('receive', array($this, 'onReceive'));
        $cli->on('close', array($this, 'onClose'));
        $cli->connect($this->uri['host'], $this->uri['port'], $this->timeout);
    }

    /**
     * [parseHeader 解析响应头部]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function parseHeader($data){
        $parts = explode("\r\n\r\n", $data, 2);
        // parts[0] = HTTP头;
        // parts[1] = HTTP主体，GET请求没有body
        $headerLines = explode("\r\n", $parts[0]);
        // HTTP协议头,方法，路径，协议[RFC-2616 5.1]
        list($status['method'], $status['uri'], $status['protocol']) = explode(' ', $headerLines[0], 3);
        //错误的HTTP请求
        if (empty($status['method']) or empty($status['uri']) or empty($status['protocol'])){
            return false;
        }
        unset($headerLines[0]);
        //解析Header
        //$this->respHeader =  \Swoole\Http\Parser::parseHeaderLine($headerLines);
        $this->status = $status;
        if (isset($parts[1])){
            $this->buffer = $parts[1];
        }
        return true;
    }

    /**
     * [onConnect 连接]
     * @param  \swoole_client $cli [description]
     * @return [type]              [description]
     */
    public function onConnect(\swoole_client $cli){
        $header = $this->method.' '.$this->uri['path'].' HTTP/1.1'. PHP_EOL;
        $header .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' . PHP_EOL;
        $header .= 'Accept-Encoding: gzip,deflate' . PHP_EOL;
        $header .= 'Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4,ja;q=0.2' . PHP_EOL;
        $header .= 'Host: '.$this->uri['host']. PHP_EOL;
        $header .= $this->userAgent . PHP_EOL;
        if (!empty($this->reqHeader)){
            foreach ($this->reqHeader as $k => $v){
                $header .= $k . ': ' . $v . PHP_EOL;
            }
        }
        $body = '';
        if ($this->post_data){
            $header .= 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL;
            $header .= 'Content-Length: ' . strlen($this->post_data) . PHP_EOL;
            $body = $this->post_data;
        }
        $cli->send($header . PHP_EOL . $body);
    }


    /**
     * [errorLog 错误日志记录]
     * @param  [type] $line [description]
     * @param  [type] $msg  [description]
     * @return [type]       [description]
     */
    public function errorLog($line, $msg){
        m_log("Line $line: $msg");
    }

    /**
     * [parseBody 解析响应体]
     * @return [type] [description]
     */
    public function parseBody()
    {
        //解析trunk
        if (isset($this->respHeader['Transfer-Encoding']) and $this->respHeader['Transfer-Encoding'] == 'chunked'){
            while(1){
                if ($this->trunk_length == 0){
                    $_len = strstr($this->buffer, "\r\n", true);
                    if ($_len === false){
                        $this->errorLog(__LINE__, "Trunk: length error, $_len");
                        return false;
                    }
                    $length = hexdec($_len);
                    //$this->errorLog(__LINE__, "Trunk Length: $_len > $length, data_length=".strlen($this->buffer));
                    if ($length == 0){
                        $this->isFinish = true;
                        return true;
                    }
                    $this->trunk_length = $length;
                    $this->buffer = substr($this->buffer, strlen($_len) + 2);
                }else{
                    //数据量不足，需要等待数据
                    if (strlen($this->buffer) < $this->trunk_length){
                        //$this->errorLog(__LINE__, "Trunk No: trunk_length={$this->trunk_length}, data_length=".strlen($this->buffer));
                        return false;
                    }
                    $this->body .= substr($this->buffer, 0, $this->trunk_length);
                    $this->buffer = substr($this->buffer, $this->trunk_length + 2);
                    //$this->errorLog(__LINE__, "Trunk OK: {$this->trunk_length}, data_length=".strlen($this->buffer));
                    $this->trunk_length = 0;
                }
            }
            return false;
        }else{
            if (strlen($this->buffer) < $this->respHeader['Content-Length']){
                return false;
            }else{
                $this->body = $this->buffer;
                $this->isFinish = true;
                return true;
            }
        }
    }



    /**
     * [gz_decode j解压]
     * @param  [type] $data [description]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    static public function gz_decode($data, $type = 'gzip'){
    	switch ($type) {
    		case 'deflate':
    			return gzdecode($data);
    			break;
    		case 'compress':
    			return gzinflate(substr($data,2,-4));
    			break;
    		default:
    			return gzdecode($data);
    			break;
    	}
    }


    /**
     * [setCookie 设置cookie]
     */
    public function setCookie(){}
    /**
     * [setUserAgent 设置头部user-agent]
     * @param [type] $userAgent [description]
     */
    public function setUserAgent($userAgent){
        $this->userAgent = $userAgent;
    }
    /**
     * [setHeader 设置请求头]
     * @param [type] $k [description]
     * @param [type] $v [description]
     */
    public function setHeader($k, $v){
        $this->reqHeader[$k] = $v;
    }


    public function  onReady($func){
        if (is_callable($func)){
            $this->onReadyCallback = $func;
        }else{
            throw new \Exception(__CLASS__.": public function is not callable.");
        }
    }


    public function onReceive($cli, $data){
        $this->buffer .= $data;
        if ($this->trunk_length > 0 and strlen($this->buffer) < $this->trunk_length){
            return;
        }
        if (empty($this->respHeader)){
            $ret = $this->parseHeader($this->buffer) ;
            if ($ret === false){
                return;
            }else{
                //header + CRLF + body
                if (strlen($this->buffer) > 0){
                    goto parse_body;
                }
            }
        }else{
            parse_body:
            if ($this->parseBody() === true and $this->isFinish){
                $compress_type = empty($this->respHeader['Content-Encoding'])?'':$this->respHeader['Content-Encoding'];
                $this->body = self::gz_decode($this->body, $compress_type);
                call_user_func($this->onReadyCallback, $this, $this->body, $this->respHeader);
            }
        }
    }
    /**
     * [onError 遇到错误]
     * @param  [type] $cli [description]
     * @return [type]      [description]
     */
    public function onError($cli){
        m_log('Connect to server failed');
    }

    /**
     * [onClose 关闭]
     * @param  [type] $cli [description]
     * @return [type]      [description]
     */
    public function onClose($cli){
    }
   
    /**
     * [get get方式发送数据]
     * @return [type] [description]
     */
    public function get(){
        $this->execute();
    }

   /**
    * [post post方式请求数据]
    * @param  array  $data [description]
    * @return [type]       [description]
    */
    public function post(array $data){
        $this->post_data = http_build_query($data);
        $this->method = 'POST';
        $this->execute();
    }
    /**
     * [close 关闭当前连接]
     * @return [type] [description]
     */
    public function close(){
        $this->cli->close();
    }
}