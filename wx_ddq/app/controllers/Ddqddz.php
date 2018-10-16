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
                    $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_LANDMARK));
                    $response = '请回复地标...';
                    break;
                case self::QUERY_CUSTOMER:
                    $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CUSTOMER));
                    $response = '请回复公司抬头...';
                    break;
                case self::QUERY_CONTACT:
                    $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CONTACT));
                    $response = '请回复客户姓名...';
                    break;
                case self::QUERY_STAFF:
                    $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_STAFF));
                    $response = '请回复同事姓名...';
                    break;
                case self::QUERY_QUOTATION:
                    $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_QUOTATION));
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

            switch ($this->weixin->message->getContent()) {
            case '?':
            case '？':
                $response = $this->weixin->getHelp();
                break 2;
            case '1':
                $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_LANDMARK));
                $response = '请回复地标...';
                break 2;
            case '2':
                $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CUSTOMER));
                $response = '请回复公司抬头...';
                break 2;
            case '3':
                $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CONTACT));
                $response = '请回复客户姓名...';
                break 2;
            case '4':
                $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_STAFF));
                $response = '请回复同事姓名...';
                break 2;
            case '5':
                $this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_QUOTATION));
                $response = '请回复目的港、船东和箱型...';
                break 2;
            }

            $userData = $this->getQueryData($this->weixin->message->getFromUserName());
            if (is_null($userData)) {
                $response = '请先选择查询类型';
                break;
            }
            $queryType = $userData['queryType'];
            $content   = $this->weixin->message->getContent();
            $this->load->library('exceldata');
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
                    if (array_key_exists('dest', $condition)) {
                        $userData['dest']      = $condition['dest'];
                        $userData['shipOwner'] = array();
                        $userData['container'] = NULL;
                    }
                    isset($condition['shipOwner']) && $userData['shipOwner'] = $condition['shipOwner'];
                    isset($condition['container']) && $userData['container'] = $condition['container'];
                    $this->saveQueryData($this->weixin->message->getFromUserName(), $userData);
                }
                if (!isset($userData['dest'])) {
                    $response = '请输入目的港';
                    break;
                }

                $result = $this->exceldata->Quote($userData['dest']);

                if (is_string($result) && $result = 'too_many_result') {
                    $response = '查询结果过多，请提供更详细的目的港信息！';
                } else if (is_array($result) && count($result) > 0) {
                    $sortDest  = array();
                    $sortPrice = array();
                    $cCtn = 'ctn'.(is_null($userData['container']) ? '20g' : strtolower($userData['container']));
                    $cEBS = 'ebs'.(is_null($userData['container']) ? '20g' : strtolower($userData['container']));
                    foreach ($result as $item) {
                        $sortDest[]  = $item['dest'];
                        $sortPrice[] = $item[$cCtn] + $item[$cEBS] + $item['amsens'];
                    }
                    array_multisort($sortDest, SORT_ASC, $sortPrice, SORT_ASC, $result);

                    if (isset($userData['shipOwner']) && count($userData['shipOwner']) > 0 && isset($userData['container'])) {
                        $response = $this->parser->parse(
                            'quote',
                            array( 'ctnType'   => $userData['container'],
                                   'shipOwner' => $userData['shipOwner'],
                                   'items'     => $result),
                            TRUE);
                    } else {
                        $response = $this->parser->parse('quote_summary', array('items' => $result), TRUE);
                    }
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
        echo EOL.round(memory_get_usage()/1048576,2).'M';
    }

    private function saveQueryData($userName, $data) {
        if (! array_key_exists('queryType', $data)) {
            log_message('info', '>>> '.__METHOD__.'() logs: queryType required');
            return FALSE;
        }
        if (! in_array($data['queryType'], self::$queryTypes)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Invalid queryType:'.$data['queryType']);
            return FALSE;
        }

        //if (($apcData = $this->cache->apc->get($userName)) === FALSE) {
        //    $apcData = array();
        //}
        //$data = array_merge($apcData, $data);
        return $this->cache->apc->save($userName, $data, 720); // valid in 10 minutes
    }

    private function getQueryData($userName) {
        if (($apcData = $this->cache->apc->get($userName)) === FALSE) {
            return NULL;
        }
        $this->cache->apc->save($userName, $apcData, 720);
        return $apcData;
    }

    public function test() {
        echo 'test OK';
    }
}
