<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ddqddz extends CI_Controller {
    const QUERY_LANDMARK  = 'landmark';
    const QUERY_CUSTOMER  = 'customer';
    const QUERY_CONTACT   = 'contact';
    const QUERY_STAFF     = 'staff';
    const QUERY_QUOTATION = 'quotation';
    private static $queryTypes = array(
        self::QUERY_LANDMARK,
        self::QUERY_CUSTOMER,
        self::QUERY_CONTACT,
        self::QUERY_STAFF,
        self::QUERY_QUOTATION
    );

    public function __construct() {
        parent::__construct();
        $this->load->driver('cache');
    }
    
    public function getaccesstoken() {
        !is_cli() && exit('forbidden');
        if (($token = $this->cache->apc->get('access_token')) === FALSE) {
            $this->config->load('weixin');
            $url    = 'https://api.weixin.qq.com/cgi-bin/token?';
            $params = http_build_query(array(
                'grant_type' => 'client_credential',
                'appid'      => $this->config->item('weixin_appID'),
                'secret'     => $this->config->item('weixin_appSecret')
            ));
            /**
            $opts   = array(
                'http' => array(
                    'method'  => 'GET',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $params
                )
            );
            $token = file_get_contents($url, FALSE, stream_context_create($opts));
             */
            $token = json_decode(file_get_contents($url.$params), TRUE)['access_token'];
            $this->cache->apc->set('access_token', $token, 5400);
        }
        log_message('info', '>>> '.__METHOD__.'() logs: access_token: '.substr($token, 0, 20).'...');
        return $token;
    }

    public function uploadmenu() {
        !is_cli() && exit('forbidden');
        exit(__METHOD__.'() not implemented');

        $url  = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getaccesstoken();
        $menu = $this->config->item('weixin_menu');
    }
    
    private function handleRequest() {
        $response = '';
        switch ($this->weixin->message->getMsgType()) {
        case WeixinMessage::MSGTYPE_EVENT:
            switch ($this->weixin->message->getEvent()) {
            case WeixinMessage::EVENT_SUBSCRIBE:
                $response = '欢迎关注！'.PHP_EOL.PHP_EOL.$this->weixin->getHelp();
                break;
            case WeixinMessage::EVENT_UNSUBSCRIBE:
                $this->weixin->responseSuccess(TRUE);
                break;
            case WeixinMessage::EVENT_CLICK:
                $this->weixin->responseSuccess(TRUE);
                break;
                switch ($this->weixin->message->getEventKey()) {
                case self::QUERY_LANDMARK:
                    $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_LANDMARK));
                    $response = '请回复地标...';
                    break;
                case self::QUERY_CUSTOMER:
                    $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CUSTOMER));
                    $response = '请回复公司抬头...';
                    break;
                case self::QUERY_CONTACT:
                    $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CONTACT));
                    $response = '请回复客户姓名...';
                    break;
                case self::QUERY_STAFF:
                    $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_STAFF));
                    $response = '请回复同事姓名...';
                    break;
                case self::QUERY_QUOTATION:
                    $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_QUOTATION));
                    $response = '请回复目的港、船东和箱型...';
                    break;
                default:
                    log_message('info', '>>> '.__METHOD__.'() logs: Unsupport EventKey: '.$this->weixin->message->getEventKey().' received');
                    $this->weixin->responseSuccess(TRUE);
                    break;
                }
                break;
            default:
                $this->weixin->responseSuccess(TRUE);
                break;
            }
            break;
        case WeixinMessage::MSGTYPE_TEXT:
            log_message('info', 'Content: '.$this->weixin->message->getContent());
            log_message('info', 'MsgId: '.$this->weixin->message->getMsgId());

            switch ($this->weixin->message->getContent()) {
            case '?':
            case '？':
                $response = $this->weixin->getHelp();
                break 2;
            case '1':
                $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_LANDMARK));
                $response = '请回复地标...';
                break 2;
            case '2':
                $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CUSTOMER));
                $response = '请回复公司抬头...';
                break 2;
            case '3':
                $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CONTACT));
                $response = '请回复客户姓名...';
                break 2;
            case '4':
                $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_STAFF));
                $response = '请回复同事姓名...';
                break 2;
            case '5':
                $this->saveQueryType($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_QUOTATION));
                $response = '请回复目的港、船东和箱型...';
                break 2;
            }

            $userData = $this->getQueryType($this->weixin->message->getFromUserName());
            if (is_null($userData)) {
                $response = '请先选择查询类型';
                break;
            }
            $queryType = $userData['queryType'];
            $content   = $this->weixin->message->getContent();
            include_once(APPPATH.'libraries/PHPExcel.php');
            include_once(APPPATH.'libraries/Exceldata.php');
            if (($exceldata = $this->cache->apc->get('exceldata')) === FALSE) {
                $this->load->library('exceldata');
                $this->cache->apc->save('exceldata', $this->exceldata, 86400);
            } else {
                $this->exceldata = $exceldata;
            }
            if (! $this->exceldata->canUse()) {
                $response = '查询功能暂不可用';
                break;
            }

            switch ($queryType) {
            case self::QUERY_LANDMARK:
                $data = $this->exceldata->landmark($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('landmark', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:landmark()');
                    $response = '查询功能暂不可用';
                }
                break;
            case self::QUERY_CUSTOMER:
                $data = $this->exceldata->customer($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('customer', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:customer()');
                    $response = '查询功能暂不可用';
                }
                break;
            case self::QUERY_CONTACT:
                $data = $this->exceldata->contact($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('contact', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:contact()');
                    $response = '查询功能暂不可用';
                }
                break;
            case self::QUERY_STAFF:
                $data = $this->exceldata->staff($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('staff', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:staff()');
                    $response = '查询功能暂不可用';
                }
                break;
            case self::QUERY_QUOTATION:
                $condition = $this->exceldata->parseQueryString($content);
                if (count($condition) > 0) {
                    $userData = array();
                    if (array_key_exists('dest', $condition)) {
                        $userData['dest']      = $condition['dest'];
                        $userData['shipOwner'] = NULL;
                        $userData['container'] = NULL;
                    }
                    if (array_key_exists('shipOwner', $condition)) {
                        $userData['shipOwner'] = $condition['shipOwner'];
                    }
                    if (array_key_exists('container', $condition)) {
                        $userData['container'] = $condition['container'];
                    }
                    $this->saveQueryType($this->weixin->message->getFromUserName(), $userData);
                }

                $result = $this->exceldata->Quote($condition['dest']);

                if (is_string($result) && $result = 'too_many_result') {
                    $response = '查询结果过多，请提供更详细的目的港信息！';
                } else if (is_array($result) && count($result) > 0) {
                    $dest  = array();
                    $price = array();
                    foreach ($result as $item) {
                        $dest[]  = $item['dest'];
                        $price[] = $item['ctn20g'] + $item['ebs20g'] + $item['amsens'];
                    }
                    array_multisort($dest, SORT_ASC, $price, SORT_ASC, $result);
                    $response = $this->parser->parse('quote', array('ctnType' => 'ALL', 'items' => $result), TRUE);
                } else {
                    $response = '什么也没有找到';
                }
                break;
            default:
                $response = '未知查询类型';
                break;
            }
            break;
        default:
            $response = $this->weixin->getHelp();
        }
        return $response;
    }

    public function index_weixin() {
        is_cli() && exit('cli forbidden');

        $this->load->library('weixin');
        if ($this->weixin->checkSignature()) {
            return;
        }

        $this->weixin->message->loadMessage();

        $this->weixin->sendResponse($this->handleRequest());
    }
    
    public function index_cli($index = '01') {
        ! is_cli() && show_404();
        
        $this->load->library('weixin');
        $xml = 'xml'.$index;
        include DATAPATH.'raw/message.php';
        $this->load->library('Weixin/WeixinMessage');
        $this->weixin->message->loadMessage(AAA::$$xml);
        $this->weixin->message->setResponseMsgType(WeixinMessage::MSGTYPE_RAW_TEXT);
        $this->weixin->sendResponse($this->handleRequest());
    }

    private function saveQueryType($userName, $data) {
        if (! array_key_exists('queryType', $data)) {
            log_message('info', '>>> '.__METHOD__.'() logs: queryType required');
            return FALSE;
        }
        if (! in_array($data['queryType'], self::$queryTypes)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Invalid queryType:'.$data['queryType']);
            return FALSE;
        }
        
        if (($apcData = $this->cache->apc->get($userName)) === FALSE) {
            $apcData = array();
        }
        $data = array_merge($apcData, $data);
        return $this->cache->apc->save($userName, $data, 720); // valid in 10 minutes
    }

    private function getQueryType($userName) {
        $apcData = $this->cache->apc->get($userName);
        if ($apcData === FALSE) {
            return NULL;
        }
        $this->cache->apc->save($userName, $apcData, 720);
        return $apcData;
    }
    
    public function test() {
        echo 'test OK';
    }
}
